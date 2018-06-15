(function () {
    var dataBallModule = angular.module('dataBallApp');

    dataBallModule.service('xmlService', function () {

        this.getJsonFromXml = function (xml) {
            var x2js = new X2JS();
            return x2js.xml_str2json(xml);
        }

        this.getGroupChilds = function (group, orgDocs) {
            var self = this;
            var documents = group.document || [];
            if (!Array.isArray(documents)) {
                documents = [documents];
            }
            var groups = [];
            if (group.group && group.group.length > 0) {
                group.group.forEach(function (grp) {
                    groups.push(self.getGroupChilds(grp, orgDocs));
                }, this);
            }

            return {
                title: group.title.phrase,
                id: group._id,
                score: group._score ? parseFloat(group._score) : 0,
                documents: documents,
                groups: groups
            }
        }

        this.getValidLinks = function (documents, groups) {
            var self = this;
            var validLinks = [];

            documents.forEach(function (document) {
                var parents = [];
                self.findParents(document._id, groups, parents);

                var maxScoreParent = _.maxBy(parents, function (pr) {
                    return pr.score;
                });

                if (maxScoreParent) {
                    validLinks.push({
                        docId: document._id,
                        groupId: maxScoreParent.id
                    });
                }
            });

            return validLinks;
        }

        this.findParents = function (docId, groups, parents) {
            var self = this;
            groups.forEach(function (grp) {
                if (self.groupIsParentOfDocument(docId, grp)) {
                    parents.push(grp);
                }

                if (grp.groups && grp.groups.length > 0) {
                    self.findParents(docId, grp.groups, parents);
                }
            });
        }

        this.groupIsParentOfDocument = function (docId, group) {
            return _.find(group.documents, function (doc) {
                return doc._refid === docId;
            }) !== undefined;
        }

        this.setNodes = function (group, nodes, validLinks) {
            var self = this;

            var docCounts = _.filter(validLinks, function (vl) {
                return vl.groupId === group.id;
            }).length;
            var grpCounts = group.groups.length;

            var label = grpCounts > 0 ? grpCounts : docCounts;
            if (label === 0) {
                label = '';
            }

            var uiGroup = 'group';
            if (label === '') {
                uiGroup = 'disable';
            }

            var grpTitle = group.title;
            if (grpTitle && grpTitle.length > 15) {
                grpTitle = grpTitle.substr(0, 14) + '...';
            }
            if (label !== '') {
                grpTitle += '\n(' + label + ')';
            }

            nodes.add({ id: "grp_" + group.id, label: grpTitle, group: uiGroup, title: group.title, shape: 'dot', size: 10 });

            group.groups.forEach(function (grp) {
                self.setNodes(grp, nodes, validLinks);
            });
        }

        this.setEdges = function (group, edges, validLinks) {
            var self = this;
            group.groups.forEach(function (grp) {
                edges.add({ from: 'grp_' + group.id, to: 'grp_' + grp.id });
                self.setEdges(grp, edges, validLinks);
            });
        }

        this.findGroupById = function (id, group) {
            if (group.id == id) {
                return [];
            } else if (group.groups) {
                var self = this;
                for (var i = 0; i < group.groups.length; i++) {
                    var path = self.findGroupById(id, group.groups[i]);
                    if (path !== null) {
                        path.unshift(group.groups[i]);
                        return path;
                    }
                }
            }
            return null;
        }
    });
})();
