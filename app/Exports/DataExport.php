<?php
namespace App\Exports;

use App\UrlPro;
use KubAT\PhpSimple\HtmlDomParser;
use App\Models\Post;

class DataExport
{

    // поле класса для хренения тела html документа
    private $dom;

    // содержит дату в формате timestamp
    private $stopDate;

    private $timestampsInDB = [];

    /*
        записывает ДОМ веб-страницы в переменную класса
    */
    private function getDOMByUrl($url) {

        if (empty($url)) return;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $this->dom = HtmlDomParser::str_get_html($response);
    }

    /*
        Возвращает даты новостей на текущей странице и ссылки на новости
        формат data[timestamp] = href
    */
    private function getNewsDateAndUrl() {
        $dates = $this->dom->find("span.time.timestamp-date");

        $data = [];
        foreach ($dates as $d) {
            $timestamp = $d->getAttribute('data-timestamp');
            $href = $d->parent()->parent()->parent()->find('a')[0]->getAttribute('href');
            $data[$timestamp] = $href;
            // print_r($d->innertext . ' (' . $timestampOfPublichArticle . ')<br>');
        }

        return $data;
    }

    /*
        Возвращает ссылку на следующую страницу ленты новостей
    */
    private function getNextPageLink () {
        $nextPageTagA = $this->dom->find("a.pagination-next")[0];

        return $nextPageTagA->getAttribute('href');
    }

    /*
        Возвращает информацию о новости:
        дата публикации в формате
        название статьи со ссылкой на ее страницу
        имя автора статьи
        список тегов статьи через запятую
    */
    private function getNewsInfo($url){

        $this->getDOMByUrl($url);

        return [
            'title' => $this->getArticleTitle(),
            'author' => $this->getArticleAuthor(),
            'tags' => $this->getArticleTags()
        ];
    }

    /*
        Возвращает заголовок статьи
    */
    private function getArticleTitle() {
        $element = $this->dom->find("h1.article__header_title")[0];
        return trim($element->innertext);
    }

    /*
        Возвращает автора статьи
    */
    private function getArticleAuthor() {
        $element = $this->dom->find("img.lazyload")[0];
        return trim($element->getAttribute('alt'));
    }

    /*
        Возвращает тэги статьи
    */
    private function getArticleTags() {
        $elements = $this->dom->find("div.tags a");

        $tags = [];
        foreach ($elements as $tag) {
            $tags[] = trim($tag->innertext);
        }

        return implode (',' , $tags);
    }



    /*
        Удаляет лишние элементы которые попали в выборку изза постраничного считывания
    */
    private function clearNewsDateAndUrl(&$newsDateAndUrl) {
        foreach ($newsDateAndUrl as $timestamp => $v) {
            if(in_array($timestamp, $this->timestampsInDB) || 
                $timestamp < $this->stopDate
            ) {
                unset($newsDateAndUrl[$timestamp]);
            }
        }
    }


    public function setStopDate($timestamp) {
        $this->stopDate = $timestamp;
    }

    public function getStopDate() {
        return $this->stopDate;
    }

    private function getDBtimestamps() {
        $rawDates = Post::where('date', '>=', $this->stopDate)->get('date');

        foreach ($rawDates as $v) {
            $this->timestampsInDB[] = $v['date'];
        }
    }



    public function process()
    {
        // 5 дней в прошлое от текущего момента
        $this->setStopDate(time() - (5 * 24 * 60 * 60));

        // записываем существующие в бд даты публикации статей из узказанного диапазона дат 
        $this->getDBtimestamps();

        // переходим на главную
        $this->getDOMByUrl("https://www.segodnya.ua/regions/odessa.html");

        $newsDateAndUrl = $this->getNewsDateAndUrl();

        do {
            // переходит на следующую страницу новостной ленты
            $this->getDOMByUrl($this->getNextPageLink());

            // добавляем новые новости к старым
            $newNews = $this->getNewsDateAndUrl();
            $newsDateAndUrl = $newsDateAndUrl + $newNews;

            // получаем самую ранню дату публикации статьи (из уже собранных)
            $latestDateOfArticles = min(array_keys($newsDateAndUrl));

        // если latestDateOfArticles входит допустимый диапазон, то переходим на следующую страницу
        } while ($latestDateOfArticles > $this->stopDate);


        // удаляем лишние элементы
        $this->clearNewsDateAndUrl($newsDateAndUrl);

        // переходим на каждую статью
        $result_data = [];
        foreach ($newsDateAndUrl as $articleTimestamp => $url) {
            $result_data[$articleTimestamp] = $this->getNewsInfo($url);
            $result_data[$articleTimestamp]['date'] = $articleTimestamp;
            $result_data[$articleTimestamp]['link'] = $url;
            Post::create($result_data[$articleTimestamp]);
        }        
    }


}
