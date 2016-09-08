<?php

class HtmlCleaner
{
  protected $tags_attributes;
  protected $allow_href_js;

  function __construct($allowed_tags = array(), $allow_href_js = false)
  {
    $this->setTagsConfig($allowed_tags);
    $this->allow_href_js = $allow_href_js;
  }

  function clean($text)
  {
    $text = trim(strip_tags($text, $this->getAllowedTagList()));//тупо удаляем ненужные теги, валидность html не проверяем
    if(!$text) return '';
    $result = $this->deleteAttributes($text);
    return $result;
  }


  function deleteAttributes($text)
  {
    $document = new DOMDocument('1.0', 'utf-8');    
    @$document->loadHTML('<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /></head>'.str_replace(array("\r\n", "\r"), "\n", $text));

    $this->recursuveDeteleAttributesFromNodeTree($document->documentElement);

    $result = $document->saveXML();//saveHTML почему-то перекодирует символы...
    $result = substr($result, stripos($result, '<body>') + 6);
    $result = substr($result, 0, strripos($result, '</body>'));

    return $result;
  }

  function recursuveDeteleAttributesFromNodeTree($node)
  {
    if($node->hasAttributes())
    {
      $allowed_attributes = $this->getAllowedAtributes($node->nodeName);
      $node_attributes = array();
      foreach($node->attributes as $attribute)
        if( !in_array($attribute->name, $allowed_attributes) )
          $node_attributes[] = $attribute->name;

      foreach($node_attributes as $atribute)
        $node->removeAttribute($atribute);
    
      if(!$this->allow_href_js && ($node instanceof DOMElement) && $node->hasAttribute('href') && preg_match("~^javascript.*~i",$node->getAttribute('href')))
        $node->removeAttribute('href');
    }
    
    if( $node->hasChildNodes())
      foreach($node->childNodes as $child)
        $this->recursuveDeteleAttributesFromNodeTree($child);
  }

  function getAllowedAtributes($tag)
  {
    if(array_key_exists($tag, $this->tags_attributes))
      return $this->tags_attributes[$tag];

    return array();
  }

  //разбор строки конфигурации
  // $string 'cut,hr[class|width|size|noshade],ap[href]'
  static function parseTagString($string)
  {
    $tags = array();
    foreach(explode(',', $string) as $str_tag)
    {
      if(!preg_match('/^([^\[]+)(\[(.+)\])?$/', $str_tag, $matches)) continue;
      $tags[trim($matches[1])] = isset($matches[3]) ? explode('|', $matches[3]) : array();
    }
    return $tags;
  }

  //форируем список тегов для функции strip_tags
  function getAllowedTagList()
  { 
    return '<' . implode('><', array_keys($this->tags_attributes)) . '>';//для использования stip_tags
  }

  function setTagsConfig($allowed_tags)
  {
    if(is_string($allowed_tags))
      $allowed_tags = self :: parseTagString($allowed_tags);

    //Параноидальная обработка. Если правильность конфига контролируется,
    //то можно оставить просто $this->tags_attributes = $allowed_tags;
    $this->tags_attributes = array();
    foreach($allowed_tags as $tag => $attributes)
      $this->tags_attributes[trim(strtolower($tag))] = array_map('strtolower', array_map('trim', $attributes));
  }

}
