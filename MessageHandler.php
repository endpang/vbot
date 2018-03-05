<?php

namespace Hanson\MyVbot;

use Hanson\MyVbot\Handlers\Contact\ColleagueGroup;
use Hanson\MyVbot\Handlers\Contact\ExperienceGroup;
use Hanson\MyVbot\Handlers\Contact\FeedbackGroup;
use Hanson\MyVbot\Handlers\Contact\Hanson;
use Hanson\MyVbot\Handlers\Type\RecallType;
use Hanson\MyVbot\Handlers\Type\TextType;
use Hanson\Vbot\Message\Image;
use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Contact\Members;

use Hanson\Vbot\Message\Emoticon;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class MessageHandler
{
    public static function messageHandler(Collection $message)
    {
        /** @var Friends $friends */
        $friends = vbot('friends');

        /** @var Members $members */
        $members = vbot('members');

        /** @var Groups $groups */
        $groups = vbot('groups');

        Hanson::messageHandler($message, $friends, $groups);
        ColleagueGroup::messageHandler($message, $friends, $groups);
        FeedbackGroup::messageHandler($message, $friends, $groups);
        ExperienceGroup::messageHandler($message, $friends, $groups);

        TextType::messageHandler($message, $friends, $groups);
        RecallType::messageHandler($message);

        if ($message['type'] === 'new_friend') {
            Text::send($message['from']['UserName'], '客官，等你很久了！感谢跟 vbot 交朋友，如果可以帮我点个star，谢谢了！https://github.com/HanSon/vbot');
            $groups->addMember($groups->getUsernameByNickname('Vbot 体验群'), $message['from']['UserName']);
            Text::send($message['from']['UserName'], '现在拉你进去vbot的测试群，进去后为了避免轰炸记得设置免骚扰哦！如果被不小心踢出群，跟我说声“拉我”我就会拉你进群的了。');
        }

        if ($message['type'] === 'emoticon' && random_int(0, 1)) {
            Emoticon::sendRandom($message['from']['UserName']);
        }

        // @todo
        if ($message['type'] === 'official') {
            vbot('console')->log('收到公众号消息:'.$message['title'].$message['description'].
                $message['app'].$message['url']);
        }

        if ($message['type'] === 'request_friend') {
            vbot('console')->log('收到好友申请:'.$message['info']['Content'].$message['avatar']);
            if (in_array($message['info']['Content'], ['echo', 'print_r', 'var_dump', 'print'])) {
                $friends->approve($message);
            }
        }
        //print_r($message);
        $re = 0;
        if($message["fromType"] == "Friend"){
            $nick = $message['from']['NickName'];
            $re = 1;
        }

        if($message["fromType"] == "Group"){
            $nick = $message['sender']['NickName'];
            if(@$message['isAt']){
                $re = 1;
            }
        }
        if($re ==1 ){

            $zi = mb_substr($message["message"],0,1,'utf-8');
            $uni = self::unicode_encode($zi);


            $var = trim($uni);
            $len = strlen($var)-1;
            $las = $var{$len};
            $url = "http://www.shufaji.com/datafile/bd/gif/".$las."/".$uni.".gif";
            //Text::send($message['from']['UserName'], "@".$nick." ".$url);
            if(!is_file(__DIR__."/img/".$uni.'.gif')){

                $img = @file_get_contents($url);

                if(!empty($img)){
                    file_put_contents(__DIR__."/img/".$uni.'.gif',$img);
                    Emoticon::send($message['from']['UserName'], __DIR__."/img/".$uni.".gif");

                }else{
                    Text::send($message['from']['UserName'], "@".$nick." 找不到这个字的笔顺".$url);
                }
            }else{
                Emoticon::send($message['from']['UserName'], __DIR__."/img/".$uni.".gif");
            }
        }


    }
    private static function unicode_encode($name)
    {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2)
        {
            $c = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0)
            {    // 两个字节的文字
                $s1 = base_convert(ord($c), 10, 16);
                $s2 = base_convert(ord($c2), 10, 16);

                if(ord($c) < 16){
                    $s1 = "0".$s1;
                }
                if(ord($c2) < 16){
                    $s2 = "0".$s2;
                }
                $str .= $s1 . $s2;
            }
            else
            {
                $str .= $c2;
            }

        }
        return $str;
    }
}
