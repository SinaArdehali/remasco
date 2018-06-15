<?php
if (!function_exists('curl_init')) {
  throw new Exception("Curl is required for this class.");
}

class Carrot2Job
{
  private $documents = array();
  private $query;
  private $source;
  private $algorithm;
  private $attributes = array();

  public function addDocument($title, $content = '', $url = '')
  {
    $this->documents[] = new Carrot2Document($title, $content, $url);
  }
  public function getDocuments()
  {
    return $this->documents;
  }

  public function setSource($source)
  {
    $this->source = $source;
  }
  public function getSource()
  {
    return $this->source;
  }

  public function setAlgorithm($algorithm)
  {
    $this->algorithm = $algorithm;
  }
  public function getAlgorithm()
  {
    return $this->algorithm;
  }

  public function setQuery($query)
  {
    $this->query = $query;
  }
  public function getQuery()
  {
    return $this->query;
  }

  public function setAttributes(array $attributes)
  {
    if (is_array($attributes)) {
      $this->attributes = $attributes;
    }
  }
 
  public function setAttribute($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  public function getAttributes()
  {
    return $this->attributes;
  }
}

class Carrot2Document
{
  private $id;
  private $title;
  private $content;
  private $url;
  private $otherFields;
  public function __construct($title, $content = '', $url = '', array $otherFields = array(), $id = null)
  {
    $this->id = $id;
    $this->title = $title;
    $this->content = $content;
    $this->url = $url;
    $this->otherFields = $otherFields;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function getUrl()
  {
    return $this->url;
  }

  public function getField($fieldName)
  {
     return isset($this->otherFields[$fieldName]) ? $this->otherFields[$fieldName] : null;
  }

  public function getOtherFields()
  {
    return $this->otherFields;
  }
}

class Carrot2Cluster
{
  private $label;
  private $score;
  private $documentIds = array();
  private $allDocumentIds;
  private $subclusters  = array();
  public function __construct($label, $score, $documentIds, $subclusters)
  {
    $this->label = $label;
    $this->score = $score;
    $this->documentIds = $documentIds;
    $this->subclusters = $subclusters;
  }
 
  public function getLabel()
  {
    return $this->label;
  }
 
  public function size()
  {
    if (!$this->allDocumentIds) {
      $this->allDocumentIds = array();
      $this->addDocumentIds($this->allDocumentIds);
    }
    return count($this->allDocumentIds);
  }

  public function getSubclusters()
  {
    return $this->subclusters;
  }

  public function getDocumentIds()
  {
    return $this->documentIds;
  }

  public function getAllDocumentIds()
  {
    return $this->allDocumentIds;
  }

  private function addDocumentIds(&$ids) 
  {
     foreach ($this->documentIds as $id) {
       $ids[$id] = $id;
     }
     foreach ($this->subclusters as $subcluster) {
       $subcluster->addDocumentIds($ids);
     }
     return $ids;
  }
}

class Carrot2Result
{
   private $documents;
   private $clusters;
   private $attributes;
   private $xml;
   public function __construct($documents = array(), $clusters = array(), $attributes = array(), $xml = null)
   {
      $this->documents = $documents;
      $this->clusters = $clusters;
      $this->attributes = $attributes;
      $this->xml = $xml;
   }
 
   public function getDocuments()
   {
      return $this->documents;
   }

   public function getClusters()
   {
      return $this->clusters;
   }

   public function getAttributes()
   {
      return $this->attributes;
   }
 
   public function getXml()
   {
      return $this->xml;
   }
}

class Carrot2Processor
{
  private $baseurl;

  public function __construct($baseurl = 'http://localhost:8080/dcs/rest')
  {
    $this->baseurl = $baseurl;
  }

  public function cluster(Carrot2Job $job)
  {
    $curl   = curl_init($this->baseurl);
    // Prepare request parameters
    $fields = array_merge($job->getAttributes(), array(
      'dcs.output.format' => 'XML'));
    $documents = $job->getDocuments();
    if (count($documents) > 0) {
       $fields['dcs.c2stream'] = $this->generateXml($documents);
    }
    self::addIfNotNull($fields, 'dcs.source', $job->getSource());
    self::addIfNotNull($fields, 'dcs.algorithm', $job->getAlgorithm());
    self::addIfNotNull($fields, 'query', $job->getQuery());

    curl_setopt_array($curl,
      array(
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => array('Content-Type: multipart/formdata'),
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => $fields
      )
    );
    $response = curl_exec($curl);
    $error = curl_errno($curl);
    if ($error !== 0) {
       throw new Carrot2Exception(curl_error($curl));
    }
    $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($httpStatus >= 400) {
       throw new Carrot2Exception('HTTP error occurred, error code: ' . $httpStatus);
    }
    return $this->extractResponse($response);
  }

  private function generateXml($documents)
  {
    $dom     = new DOMDocument('1.0', 'UTF-8');
    $resultsElement = $dom->createElement('searchresult');
    $dom->appendChild($resultsElement);
    foreach ($documents as $document) {
      $documentElement = $dom->createElement('document');
      $this->appendTextField($dom, $documentElement, 'title', $document->getTitle());
      $this->appendTextField($dom, $documentElement, 'snippet', $document->getContent());
      $this->appendTextField($dom, $documentElement, 'url', $document->getUrl());
      $resultsElement->appendChild($documentElement);
    }
    return $dom->saveXML();
  }
  private function appendTextField($dom, $elem, $name, $value)
  {
    $text = $dom->createElement($name);
    $text->appendChild($dom->createTextNode((string)$value));
    $elem->appendChild($text);
  }
  /**
   * Extracts Carrot2Results from the XML response.
   */
  private function extractResponse($rawXml)
  {
    $xml = new SimpleXMLElement($rawXml);
    return new Carrot2Result($this->extractDocuments($xml), 
                             $this->extractClusters($xml->xpath('/searchresult/group')),
                             $this->extractAttributes($xml->xpath('/searchresult/attribute')),
                             $rawXml);
  }
  private function extractDocuments($xml)
  {
    $documents = array();
    foreach ($xml->xpath('/searchresult/document') as $documentElement) {
      $document = new Carrot2Document(
        (string)$documentElement->title,
        (string)$documentElement->snippet,
        (string)$documentElement->url,
        $this->extractAttributes($documentElement->xpath('field')),
        (string)$documentElement['id']
      );
      $documents[] = $document;
    }
    return $documents;
  }
  private function extractClusters($groupElements)
  {
    $clusters = array();
    foreach ($groupElements as $group) {
      $documentIds = array();
      foreach ($group->xpath('document') as $document) {
        $documentIds []= (string)$document['refid'];
      }
      $subclusters = $this->extractClusters($group->xpath('group'));
      $cluster = new Carrot2Cluster(
        (string)$group->title->phrase,
        (string)$group['score'],
        $documentIds,
        $subclusters
      );
      $clusters[] = $cluster;
    }
    return $clusters;
  }
  private function extractAttributes($attributeElements)
  {
     $attributes = array();
     foreach($attributeElements as $attribute) {
        $key = $attribute['key'];
        $valueElement = $attribute->xpath('value');
        if (count($valueElement) > 0) {
          $value = $valueElement[0]['value'];
          if ($value) {
             $attributes[(string)$key] = (string)$value;
          }
        }
     }
     return $attributes;
  }
  private static function addIfNotNull(&$array, $key, $value)
  {
    if ($value) {
      $array[$key] = $value;
    }
  }
}

class Carrot2Exception extends Exception {
}

