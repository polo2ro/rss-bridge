<?php
class AllocineFRBridge extends BridgeAbstract{


    const MAINTAINER = "superbaillot.net";
    const NAME = "Allo Cine Bridge";
    const URI = "http://www.allocine.fr";
    const DESCRIPTION = "Bridge for allocine.fr";
    const PARAMETERS = array( array(
        'category'=>array(
            'name'=>'category',
            'type'=>'list',
            'required'=>true,
            'exampleValue'=>'Faux Raccord',
            'title'=>'Select your category',
            'values'=>array(
                'Faux Raccord'=>'faux-raccord',
                'Top 5'=>'top-5',
                'Tueurs en Séries'=>'tueurs-en-serie'
            )
        )
    ));

    public function getURI(){
        switch($this->getInput('category')){
        case 'faux-raccord':
            $uri = static::URI.'video/programme-12284/saison-24580/';
            break;
        case 'top-5':
            $uri = static::URI.'video/programme-12299/saison-22542/';
            break;
        case 'tueurs-en-serie':
            $uri = static::URI.'video/programme-12286/saison-22938/';
            break;
        }

        return $uri;
    }

    public function getName(){
        return self::NAME.' : '
            .array_search(
                $this->getInput('category'),
                self::PARAMETERS[$this->queriedContext]['category']['values']
            );
    }

    public function collectData(){

        $html = $this->getSimpleHTMLDOM($this->getURI())
            or $this->returnServerError("Could not request ".$this->getURI()." !");

        $category=array_search(
                $this->getInput('category'),
                self::PARAMETERS[$this->queriedContext]['category']['values']
            );


        foreach($html->find('figure.media-meta-fig') as $element)
        {
            $item = array();

            $title = $element->find('div.titlebar h3.title a', 0);
            $content = trim($element->innertext);
            $figCaption = strpos($content, $category);

            if($figCaption !== false)
            {
                $content = str_replace('src="/', 'src="'.static::URI, $content);
                $content = str_replace('href="/', 'href="'.static::URI, $content);
                $content = str_replace('src=\'/', 'src=\''.static::URI, $content);
                $content = str_replace('href=\'/', 'href=\''.static::URI, $content);
                $item['content'] = $content;
                $item['title'] = trim($title->innertext);
                $item['uri'] = static::URI . $title->href;
                $this->items[] = $item;
            }
        }
    }

    public function getCacheDuration(){
        return 25200; // 7 hours
    }
}
