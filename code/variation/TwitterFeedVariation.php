<?php

/**
 * Really just developed as an example of what you can do with this module
 */
class TwitterFeedVariation extends PersonalisationVariation
{

    public static $db = array(
        "AccountName" => "Varchar(255)",
        "NoOfTweets" => "Int"
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $acctName = new TextField("AccountName", "Account Name");
        $fields->push($acctName);

        return $fields;
    }

    public function helperText()
    {
        return "lets you display Twitter feed items from a given Twitter account as the output.";
    }

    public function render(ContextProvider $context, Controller $controller = null)
    {
        return $controller->customise(array("Tweets" => $this->getTweets()))->renderWith('TwitterFeedVariation');
    }

    /*
     * Retrieve the latest tweets
     */
    public function getTweets()
    {
        if (!$this->AccountName) {
            return null;
        }

        if ($feed = new RestfulService('http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=' . $this->AccountName)) {
            $feedXML = $feed->request()->getBody();

            if ($feedXML) {
                $latestTweets = new ArrayList();
                $tweets = $feed->getValues($feedXML, 'channel', 'item');
                //limit to 5
                $i = 0;
                foreach ($tweets as $tweet) {
                    if ($i <= $this->NoOfTweets) {
                        $date = $tweet->getField('pubDate');
                        $t = new DataObject();
                        $t->Headline = $this->twitifyText(str_replace($this->AccountName . ': ', '', $tweet->getField('description')));
                        $t->Date = date('d M Y', strtotime($date));
                        $latestTweets->push($t);
                        $i++;
                    }
                }
                return $latestTweets;
            } else {
                return null;
            }
        }
    }

    public function twitifyText($headline)
    {
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        if (preg_match($reg_exUrl, $headline, $url)) {
            // make the urls hyper links
            return preg_replace($reg_exUrl, "<a href=\"{$url[0]}\">{$url[0]}</a> ", $headline);
        } else {
            // if no urls in the text just return the text
            return $headline;
        }
    }
}
