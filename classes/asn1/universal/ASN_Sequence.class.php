<?php
/*
 * This file is part of PHPASN1 written by Friedrich Große.
 * 
 * Copyright © Friedrich Große, Berlin 2012
 * 
 * PHPASN1 is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHPASN1 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHPASN1.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PHPASN1;

class ASN_Sequence extends ASN_Object implements Parseable {
	
	public function __construct(ASN_Object $child1 = null, ASN_Object $child2 = null, ASN_Object $childN = null) {
		$this->value = array();
		
        $children = func_get_args();        
        $this->addChildren($children);		
	}	
    
    public function getType() {
        return Identifier::SEQUENCE;
    }
    
    protected function calculateContentLength() {
        $length = 0;
        foreach($this->value as $component) {
            $length += $component->getObjectLength();
        }             
        return $length;
    }
    
	protected function getEncodedValue() {		
		$result = '';
		foreach($this->value as $component) {
			$result .= $component->getBinary();
		}
		return $result;
	}	
	
	public function addChild(ASN_Object $child) {
		$this->value[] = $child;
	}
    
    public function addChildren(array $children) {
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }
	
	public function __toString() {
	    $className = get_called_class();
	    $simpleClassName = substr($className, strpos($className, '\\') + 1);
		return "[{$simpleClassName}]";       
	}
    
    public function getNumberofChildren() {
        return count($this->value);
    }
    
    public function getChildren() {
        return $this->value;
    }
    
    public static function fromBinary(&$binaryData, &$offsetIndex=0) {                
        self::verifyIdentifier($binaryData[$offsetIndex++], $offsetIndex);
        $contentLength = self::parseContentLength($binaryData, $offsetIndex);        
        
        $children = array();
        $octetsToRead = $contentLength;
        while($octetsToRead > 0) {
            $newChild = ASN_Object::fromBinary($binaryData, $offsetIndex);
            $octetsToRead -= $newChild->getObjectLength();     
            $children[] = $newChild;      
        }
                
        $parsedObject = new self();
        $parsedObject->addChildren($children);
        $parsedObject->setContentLength($contentLength);
        return $parsedObject;
    }
    
    protected static function verifyIdentifier($identifierOctet, $offsetForExceptionHandling) {
        self::parseIdentifier($identifierOctet, Identifier::SEQUENCE, $offsetForExceptionHandling);
    }    
}
?>