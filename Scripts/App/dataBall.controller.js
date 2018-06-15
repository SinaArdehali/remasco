(function () {
    var dataBallModule = angular.module('dataBallApp');

    dataBallModule.controller('dataBallController', [
      '$scope',
      'ngDialog',
      'dataBallService',
      'xmlService',
      'uiService',
      function ($scope, ngDialog, dataBallService, xmlService, uiService) {

          var nodes = new vis.DataSet([]);
          var edges = new vis.DataSet([]);
          var container = document.getElementById('netContainer');

          $scope.existDocs = {
              groupId: "initValue",
              nodes: [],
              edges: []
          };
          $scope.historics = [];
          $scope.themes = [];

          var options = {
              autoResize: true,
              width: '100%',
              height: '100%',
              groups: {
                  root: {
                      color: {
                          background: '#94E191',
                          border: '#2F8927',
                          highlight: {
                              border: '#2F8927',
                              background: '#12AD2B'
                          }
                      }
                  },
                  document: {
                      color: {
                          background: '#E8B7E5',
                          border: '#7B2266',
                          highlight: {
                              border: '#7B2266',
                              background: '#DF81D6',
                          }
                      }
                  },
                  disable: {
                      color: {
                          background: '#ddd',
                          border: '#ccc',
                          highlight: {
                              border: '#ccc',
                              background: '#ddd',
                          },
                          hover: {
                              background: '#ddd'
                          }
                      }
                  }
              },
              physics: {
                  maxVelocity: 50,
                  stabilization: {
                      updateInterval: 5
                  }
              }
          };

          $scope.addTheme = function () {
              $scope.newTheme = {
                  themeName: ''
              };
              ngDialog.open({
                  name: 'addTheme',
                  template: 'Scripts/App/Theme.html',
                  className: 'ngdialog-theme-flat',
                  scope: $scope,
                  showClose: false
              });
          }

          $scope.cancelTheme = function () {
              ngDialog.close('addTheme');
          }

          $scope.saveTheme = function () {
              var existing = _.find($scope.themes, function (theme) {
                  return theme.themeName === $scope.newTheme.themeName;
              });

              if (!existing) {
                  dataBallService.addTheme({
                      themeName: $scope.newTheme.themeName
                  }, function (data) {

                      $scope.themes.push({
                          idTheme: data.idTheme,
                          themeName: $scope.newTheme.themeName
                      });
                      ngDialog.close('addTheme');
                  }, function (err) {
                      uiService.error(err);
                  });


              } else {
                  uiService.error('Le thème ' + $scope.newTheme.themeName + ' existe déjà.');
              }
          }

          $scope.removeTheme = function (theme) {
              uiService.confirm('Voulez-vous supprimer le thème "' + theme.themeName + '" ?', function () {
                  dataBallService.removeTheme(theme.idTheme, function () {
                      $scope.themes = _.filter($scope.themes, function (tm) {
                          return tm.idTheme !== theme.idTheme;
                      });

                  }, function (err) {
                      uiService.error(err);
                  });

              });

          }

          $scope.removeHistoricFromTheme = function (theme, historic) {
              uiService.confirm('Voulez-vous supprimer cette recherche du thème "' + theme.themeName + '" ?', function () {
                  dataBallService.removeHistoricFromTheme(historic.idHistoric, theme.idTheme, function () {
                      theme.historics = _.filter(theme.historics, function (h) {
                          return h.idHistoric !== historic.idHistoric;
                      });

                  }, function (err) {
                      uiService.error(err);
                  });

              });
          }


          // Function to decode string
          String.prototype.decode = function(){
              return decodeURIComponent(escape(this));
          }

          // Function to encode string
          String.prototype.encode = function() {
              return unescape(encodeURIComponent(this));
          }

          // Encode the string before send to Lingo with encode() function
          $scope.search = function () {
              resetSearch();
              var searchQuery = $scope.query;
              dataBallService.search(searchQuery, function (data) {
                  if (data) {
                    loadXML(data, searchQuery, true);
                  } 
                  else {
                    uiService.alert('Aucun résultats.');
                  }

              }, function (err) {

              });
          }

          function resetSearch() {
              edges.clear();
              nodes.clear();
              document.getElementById('loadingContainer').style.display = 'block';
              document.getElementById('loadingContainer').style.opacity = 1;
              document.getElementById('loader').style.width = '5px';
              $scope.details = {};
              $scope.existDocs = {
                  groupId: "initValue",
                  nodes: [],
                  edges: []
              };
          }

          

          function loadXML(data, searchQuery, saveResult) {

              var jsonData = xmlService.getJsonFromXml(data);

              if (jsonData.searchresult) {
                  if (saveResult) {
                      $scope.saveSearch(searchQuery, data);
                  }
                  var result = jsonData.searchresult;

                  if (result.group && result.group.length > 0) {

                      $scope.groups = result.group;
                      $scope.documents = result.document;

                      $scope.resultSet = [];
                      $scope.groups.forEach(function (group) {
                          $scope.resultSet.push(xmlService.getGroupChilds(group, $scope.documents));
                      }, this);

                      $scope.validLinks = xmlService.getValidLinks($scope.documents, $scope.resultSet);
   
                      nodes.add({ id: "root_node", group: 'root', label: searchQuery, shape: 'ellipse' });

                      $scope.resultSet.forEach(function (group) {
                          edges.add({ from: 'root_node', to: 'grp_' + group.id });
                          xmlService.setNodes(group, nodes, $scope.validLinks);
                          xmlService.setEdges(group, edges, $scope.validLinks);
                      });


                      var data = {
                          nodes: nodes,
                          edges: edges,
                      };

                      var network = new vis.Network(container, data, options);
                      var totalIterations = nodes.length + edges.length;
                      var maxWidth = document.getElementById('loadingContainer').offsetWidth - 3;

                      network.on("stabilizationProgress", function (params) {
                          var minWidth = 5;
                          var widthFactor = params.iterations / totalIterations;
                          var width = Math.max(minWidth, maxWidth * widthFactor);

                          if (width > maxWidth) {
                              width = maxWidth;
                          }

                          document.getElementById('loader').style.width = width + 'px';
                      });

                      network.once("stabilizationIterationsDone", function () {
                          document.getElementById('loader').style.width = maxWidth + 'px';
                          document.getElementById('loadingContainer').style.opacity = 0;
                          setTimeout(function () { document.getElementById('loadingContainer').style.display = 'none'; }, 500);
                      });

                      network.on('selectNode', function (params) {
                          if (params.nodes[0].indexOf('grp_') !== -1) {
                              var id = params.nodes[0].replace('grp_', '');
                              $scope.details = {
                                  type: 'group'
                              }
                              var tgGroups = xmlService.findGroupById(id, { groups: $scope.resultSet });
                              if (tgGroups.length > 0) {
                                  var group = tgGroups[tgGroups.length - 1];
                                  if (group && group.groups && group.groups.length > 0) {
                                      $scope.details.groups = group.groups;
                                  }
                              }

                              var docs = _.filter($scope.validLinks, function (vl) {
                                  return vl.groupId == id;
                              });
                              if (docs.length > 0) {
                                  var docIds = _.map(docs, 'docId');

                                  var docsToAdd = _.filter($scope.documents, function (doc) {
                                      return docIds.indexOf(doc._id) !== -1;
                                  });
                                  if (!$scope.details.groups) {
                                      $scope.details.documents = docsToAdd;
                                  }
                                  if ($scope.existDocs.groupId !== id) {

                                      edges.remove($scope.existDocs.edges);
                                      nodes.remove($scope.existDocs.nodes);
                                      $scope.existDocs = {
                                          groupId: id,
                                          nodes: [],
                                          edges: []
                                      };

                                      var newNodes = [];
                                      var newEdges = [];

                                      docsToAdd.forEach(function (doc) {
                                          $scope.existDocs.nodes.push("doc_" + doc._id);
                                          var label = doc.title;
                                          if (label && label.length > 20) {
                                              label = label.substr(0, 19) + '...';
                                          }
                                          newNodes.push({ id: "doc_" + doc._id, group: 'document', label: label, title: doc.title, shape: 'box' });
                                          newEdges.push({ from: "grp_" + id, to: "doc_" + doc._id });
                                      });

                                      nodes.add(newNodes);
                                      $scope.existDocs.edges = edges.add(newEdges);
                                  }
                              }
                          } else {
                              $scope.details = {
                                  type: 'document'
                              }
                              if (params.nodes[0].indexOf('doc_') !== -1) {
                                  var id = params.nodes[0].replace('doc_', '');
                                  var document = _.find($scope.documents, function (doc) {
                                      return doc._id == id;
                                  });
                                  $scope.details.document = angular.copy(document);
                                  if (document && document.sources) {
                                      if (!Array.isArray(document.sources.source)) {
                                          $scope.details.document.sources = [document.sources.source];
                                      } else {
                                          $scope.details.document.sources = document.sources.source;
                                      }
                                  }
                              } else if (params.nodes[0].indexOf('root_') !== -1) {
                                  $scope.details.document = {
                                      title: result.query
                                  };
                              }

                          }
                          $scope.$apply();
                      });
                      network.on('doubleClick', function (params) {
                          if (params.nodes && params.nodes.length > 0 && params.nodes[0].indexOf('grp_') !== -1) {
                              var id = params.nodes[0].replace('grp_', '');
                              var tgGroups = xmlService.findGroupById(id, { groups: $scope.resultSet });
                              if (tgGroups.length > 0) {

                                  _.forEach(tgGroups, function (g) {
                                      var title = g.title;
                                      if (Array.isArray(title)) {
                                          title = title[0];
                                      }

                                      $scope.query += " " + title;
                                  });

                                  $scope.$apply();
                                  $scope.search();
                              }
                          }
                      });
                  }
                  else {
                      //alert no result
                  }
              }
          }

          $scope.reloadSearch = function (historic) {
              resetSearch();
              $scope.query = historic.query;
              loadXML(historic.XMLfile);
          }

          $scope.saveSearch = function (query, xml) {

              dataBallService.addHistory({
                  query: query,
                  xml: xml
              }, function (data) {
                  $scope.historics.push({
                      idHistoric: data.idHistoric,
                      query: query,
                      XMLfile: xml
                  });
              }, function (err) {
                  uiService.error(err);
              });
          }

          $scope.init = function () {
              dataBallService.load(function (data) {

                  $scope.themes = data.themes || [];
                  $scope.historics = data.historics || [];

              }, function (err) {
                  uiService.error(err);
              });
          }

          $scope.removeHistoric = function (historic) {
              uiService.confirm("Attention ! La suppression dans l'historique supprime aussi la recherche dans le thème.Voulez-vous supprimer cette recherche ?", function () {
                  dataBallService.removeHistory(historic.idHistoric, function () {
                      $scope.historics = _.filter($scope.historics, function (h) {
                          return h.idHistoric !== historic.idHistoric;
                      });
                      if ($scope.themes && $scope.themes.length > 0) {
                          $scope.themes.forEach(function (theme) {
                              theme.historics = _.filter(theme.historics, function (h) {
                                  return h.idHistoric !== historic.idHistoric;
                              });
                          });
                      }


                  }, function (err) {
                      uiService.error(err);
                  });
              });
          }

          $scope.addHistoricToTheme = function (historic) {
              
              if ($scope.themes && $scope.themes.length > 0) {
                  $scope.link = {
                      selectedHistoric: historic,
                      selectedTheme: $scope.themes[0].idTheme
                  }
                  ngDialog.open({
                      name: 'addLink',
                      template: 'Scripts/App/Link.html',
                      className: 'ngdialog-theme-flat',
                      scope: $scope,
                      showClose: false
                  });
              } else {
                  uiService.error("Vous n'avez pas créé de thème. Ajout impossible.");
              }

          }

          $scope.cancelLink = function () {
              ngDialog.close('addLink');
          }

          $scope.saveLink = function () {
              dataBallService.addHistoricToTheme($scope.link.selectedHistoric.idHistoric, $scope.link.selectedTheme, function () {
                  var theme = _.find($scope.themes, function (theme) {
                      return theme.idTheme == $scope.link.selectedTheme;
                  });
                  if (theme) {
                      if (!theme.historics) {
                          theme.historics = [];
                      }
                      theme.historics.push($scope.link.selectedHistoric);
                  }
                  ngDialog.close('addLink');
              }, function (err) {
                  uiService.error(err);
              });
          }

          $scope.init();

      }]);
})();
