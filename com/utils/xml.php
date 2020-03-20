<?php

/**
 * @Author: zhangcb
 * @Date:   2017-10-29 10:43:26
 * @Last Modified by:   zhangcb
 * @Last Modified time: 2017-11-01 16:25:51
 */
class XmlUtils
{
	public static function readXMLString($xmlStr){
		$root = simplexml_load_string($xmlStr);
		return self::xmlObjToArr($root);
	}
	public static function readXMLFile($xmlFile){
		if(!file_exists($xmlFile)){
			throw new Exception("找不到XML文件:".$xmlFile);
		}
		$content = file_get_contents($xmlFile);
		return self::readXMLString($content);
	}
	public static function saveXMLFile($xmlFile,$content){

	}
	static function xmlObjToArr($obj) { 
		$namespace = $obj->getDocNamespaces(true); 
		$namespace[NULL] = NULL; 

		$children = array(); 
		$attributes = array(); 
		$name = strtolower((string)$obj->getName()); 

		$text = trim((string)$obj); 
		if( strlen($text) <= 0 ) { 
			$text = NULL; 
		} 

	        // get info for all namespaces 
		if(is_object($obj)) { 
			foreach( $namespace as $ns=>$nsUrl ) { 
	                // atributes 
				$objAttributes = $obj->attributes($ns, true); 
				foreach( $objAttributes as $attributeName => $attributeValue ) { 
					$attribName = strtolower(trim((string)$attributeName)); 
					$attribVal = trim((string)$attributeValue); 
					if (!empty($ns)) { 
						$attribName = $ns . ':' . $attribName; 
					} 
					$attributes[$attribName] = $attribVal; 
				} 

	                // children 
				$objChildren = $obj->children($ns, true); 
				foreach( $objChildren as $childName=>$child ) { 
					$childName = strtolower((string)$childName); 
					if( !empty($ns) ) { 
						$childName = $ns.':'.$childName; 
					} 
					$children[$childName][] = self::xmlObjToArr($child); 
				} 
			} 
		} 

		return array( 
			'name'=>$name, 
			'text'=>$text, 
			'attributes'=>$attributes, 
			'children'=>$children 
			); 
	} 
}