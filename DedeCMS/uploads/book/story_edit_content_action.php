<?php
/**
 * @version        $Id: story_edit_content_action.php 1 9:02 2010年9月25日Z 蓝色随想 $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/../member/config.php");
CheckRank(0,0);
include_once(DEDEINC. "/oxwindow.class.php");
require_once(dirname(__FILE__). "./include/story.func.php");
if( empty($chapterid)
|| (!empty($addchapter) && !empty($chapternew)) )
{
    if(empty($chapternew))
    {
        ShowMsg("由于你发布的内容没选择章节，系统拒绝发布！","-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM #@__story_chapter WHERE bookid='$bookid' AND mid='$cfg_ml->M_ID' ORDER BY chapnum DESC");
    if(is_array($row))
    {
        $nchapnum = $row['chapnum']+1;
    }
    else
    {
        $nchapnum = 1;
    }
    $query = "INSERT INTO `#@__story_chapter`(`bookid`,`catid`,`chapnum`,`mid`,`chaptername`,`bookname`)
            VALUES ('$bookid', '$catid', '$nchapnum', '$cfg_ml->M_ID', '$chapternew','$bookname');";
    $rs = $dsql->ExecuteNoneQuery($query);
    if($rs)
    {
        $chapterid = $dsql->GetLastID();
    }
    else
    {
        ShowMsg("增加章节失败，请检查原因！","-1");
        exit();
    }
}

//获得父栏目
$nrow = $dsql->GetOne("SELECT * FROM #@__story_catalog WHERE id='$catid' ");
$bcatid = $nrow['pid'];
$booktype = $nrow['booktype'];
if(empty($bcatid))
{
    $bcatid = 0;
}
if(empty($booktype))
{
    $booktype = 0;
}
$addtime = time();
$inQuery = "
   UPDATE `#@__story_content` SET `title`='$title',`bookname`='$bookname',
   `chapterid`='$chapterid',`sortid`='$sortid',`body`=''
  WHERE id='$cid' AND mid = '{$cfg_ml->M_ID}'
";
if(!$dsql->ExecuteNoneQuery($inQuery))
{
    ShowMsg("更新数据时出错，请检查！".str_repolace("'","`",$dsql->GetError().$inQuery),"-1");
    exit();
}
WriteBookText($cid, addslashes($body));

//生成HTML
//$artUrl = MakeArt($arcID,true);
//if($artUrl=="") $artUrl = $cfg_book_url."/story.php?id={$cid}";
if(!isset($artUrl) || $artUrl=="")
{
    $artUrl = $cfg_cmspath. "/book/story.php?id={$cid}";
}

//返回成功信息
$msg = "
　　请选择你的后续操作：
<a href='story_content_edit.php?cid={$cid}'><u>继续编辑</u></a>
&nbsp;&nbsp;
<a href='$artUrl' target='_blank'><u>预览内容</u></a>
&nbsp;&nbsp;
<a href='../book/book.php?id=$bookid' target='_blank'><u>预览图书</u></a>
&nbsp;&nbsp;
<a href='story_list_content.php?bookid={$bookid}'><u>本书所有内容</u></a>
&nbsp;&nbsp;
<a href='mybooks.php'><u>管理所有图书</u></a>
";
$wintitle = "成功修改文章！";
$wecome_info = "连载管理::发布文章";
$win = new OxWindow();
$win->AddTitle("成功修改文章：");
$win->AddMsgItem($msg);
$winform = $win->GetWindow("hand", "&nbsp;", false);
$win->Display();
