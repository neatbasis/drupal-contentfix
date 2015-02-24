<?php
namespace ContentFix;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class App {
    private $capsule;
    function __construct($config){
        print_r($config);
        $config['charset'] = 'utf8';
        $config['collation'] = 'utf8_unicode_ci';
        $capsule = new Capsule;
        
        $capsule->addConnection($config);

        $capsule->setEventDispatcher(new Dispatcher(new Container));
        
        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();
        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();
        
    }
    public function run(){
        
        $nodes = FieldDataBody::where('body_format', '=', 'full_html')
                ->orWhere('body_format', '=', 'filtered_html')
        //->limit(40)
        ->get();
        $progressBar = new \ProgressBar\Manager(0, count($nodes));
        echo "Processing articles".PHP_EOL;
        foreach($nodes as $node){
            $progressBar->advance();
            $modified = false;
            $doc = new \DOMDocument();
            $body = '<?xml encoding="UTF-8"><html><body>'.$node->body_value.'</body></html>';
            $doc->loadHTML($body);
            //echo $body;
            
            // dirty fix
            foreach ($doc->childNodes as $item)
                if ($item->nodeType == XML_PI_NODE)
                    $doc->removeChild($item); // remove hack
            $doc->encoding = 'UTF-8'; // insert proper
 

            foreach( $doc->getElementsByTagName("img") as $pnode ) {
                //$divnode = $dom->createElement("div", $pnode->nodeValue);
                //$dom->replaceChild($divnode, $pnode);
                $pnode->removeAttribute('style');
                $pnode->removeAttribute('class');
                $pnode->removeAttribute('width');
                $pnode->removeAttribute('height');
                $pnode->setAttribute('class', 'img-responsive');
            }

            foreach( $doc->getElementsByTagName("iframe") as $pnode ) {
                
                if(!self::isResponsiveIframe($pnode)){
                    $newnode = $this->responsifyIframe($doc, $pnode);
                    $pnode->parentNode->replaceChild($newnode, $pnode);
                    
                }else{
                    
                }
            }
            
            $doc->removeChild($doc->doctype);
            $doc->replaceChild($doc->firstChild->firstChild, $doc->firstChild);
            $entity = mb_convert_encoding($this->cleanOutput($doc), 'UTF-8','HTML-ENTITIES');
            $node->body_value = $entity;
            $node->save();
            //echo $entity.PHP_EOL;
            //$node->
        }//end foreach
    }

    
    public function isResponsiveIframe($node){
        if(self::hasClass($node->parentNode, 'embed-responsive'))
            return true;
        
       return false;
    }
    
    public function responsifyIframe(&$doc, $node){
        
        //get ratio and corresponding class
        $ratio = self::getRatio($node);
        $class = self::embedResponsiveClass($ratio);
        
        //create parent div
        $divnode = $doc->createElement('div');
        $divnode->setAttribute('class', $class);
        
        // clone, modify and append
        $newnode = $node->cloneNode(true);
        $newnode->removeAttribute('height');
        $newnode->removeAttribute('width');
        $newnode->removeAttribute('frameborder');
        $newnode->removeAttribute('allowfullscreen');
        $newnode->removeAttribute('scrolling');
        $newnode->setAttribute('allowfullscreen',1);
        $newnode->setAttribute('class','embed-responsive-item');
        $divnode->appendChild($newnode);
        return $divnode;
    }
    
    public static function hasClass($node, $classname){
        if(!$node->hasAttribute('class'))
            return false;
        $classes = explode(' ', $node->getAttribute('class'));
        foreach($classes as $class){
            if($class==$classname)
                return true;
        }
        return false;
    }
    public static function getRatio($node){
        if(!self::hasRatio($node))
            return floatval(16/9);
        return floatval($node->getAttribute('width')/$node->getAttribute('height'));
    }
    public static function hasRatio($node){
        if($node->hasAttribute('width') && $node->hasAttribute('height'))
            return true;
        return false;
    }
    public static function embedResponsiveClass($ratio){
        return $ratio > 1.55555 ? 'embed-responsive embed-responsive-16by9':'embed-responsive embed-responsive-4by3';
    }
    public function cleanOutput($doc){
        return substr(substr($doc->saveHTML(), 6),0, -8);
    }
    
}