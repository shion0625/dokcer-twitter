<?php
use Classes\Post\GetHomePosts;

//投稿内容をデータベースから取得

//データベースに投稿内容を保存
$get_post_db = new GetHomePosts();
$user_posts = $get_post_db->getHomePosts();
    // header("Location: " . $_SERVER['PHP_SELF']);
?>

<script type="text/javascript">
    const username = <?php echo json_encode($_SESSION['username']);?>;
    const userId =<?php echo json_encode($_SESSION['userID']);?>;

    const changeStrongText = () => {
        let selObj = getSelection();
        if(!selObj.rangeCount){
            return;
        }
        let range = selObj.getRangeAt(0);
        let selectText = selObj.toString();
        sanitize(range);
        if(!$("#js-post-content").find('b').length){
            console.log('0');
            let boldNode = document.createElement('b');
            let df = range.extractContents();
            boldNode.appendChild(df);
            range.insertNode(boldNode);
            return;
        }
        checkNodes(range, selectText)
    }

    // nodeは現在調べているノード、rangeは着色したい範囲のRange
function checkNodes(range, selectText){
    $("#js-post-content").find('b').each(( index, node)=>{
        // 新しいRangeを作る
        let nodeRange = new Range();
        // nodeRangeの範囲をnodeを囲むように設定
        nodeRange.selectNode(node);

        if(node.innerHTML.toString() == ""){
            node.remove();
        }
        console.log(range.compareBoundaryPoints(Range.START_TO_START, nodeRange));
        console.log(range.compareBoundaryPoints(Range.END_TO_END, nodeRange));
        if (range.compareBoundaryPoints(Range.START_TO_START, nodeRange) <= 0 &&
            range.compareBoundaryPoints(Range.END_TO_END, nodeRange) >= 0){
          // nodeRangeはrangeに囲まれている
          // 中にあるb要素を消して全体にb要素で囲む
            // let boldNode = document.createElement('b');
            console.log('囲まれている');
            let df = range.extractContents();
            let textNode = document.createTextNode(selectText);
            range.insertNode(textNode);
            return false;
        }
        else if (range.compareBoundaryPoints(Range.START_TO_END, nodeRange) <0 ||
            range.compareBoundaryPoints(Range.END_TO_START, nodeRange) >0){
          // nodeRangeとrangeは重なっていない
            console.log('重なっていない');
            let boldNode = document.createElement('b');
            let df = range.extractContents();
            boldNode.appendChild(df);
            range.insertNode(boldNode);
        }else if(range.compareBoundaryPoints(Range.START_TO_START, nodeRange) > 0 &&
            range.compareBoundaryPoints(Range.END_TO_END, nodeRange) < 0) {
            console.log('rangeがノードに囲まれている。');
            let df = range.extractContents();
            let textNode = document.createTextNode("</b>" +selectText + "<b>");
            range.insertNode(textNode);
            return false;
            }
        else {
            console.log('このノードは一部rangeに含まれている');
          // このノードは一部rangeに含まれている
            let df = range.extractContents();
            let textNode = document.createTextNode(selectText);
            range.insertNode(textNode);
            return false;
        }
    })
}

    function sanitize(range){
        // 開始点がテキストノードの中だったら
        if(range.startContainer.nodeType == Node.TEXT_NODE){
          // テキストノードをRangeの開始点の位置で2つに分ける
            var latter = range.startContainer.splitText(range.startOffset);
          // Rangeの開始点をテキストノードの外側にする
            range.setStartBefore(latter);
        }
        // 終了点にも同様の処理
        if(range.endContainer.nodeType == Node.TEXT_NODE){
            var latter = range.endContainer.splitText(range.endOffset);
            range.setEndBefore(latter);
        }
    }

    // const getSelectText = ()=> {
    //     let selObj = window.getSelection();
    //     if(!selObj.rangeCount) return;
    //     let range = selObj.getRangeAt(0);
    //     console.log(range);
    //     return {
    //         range: range,
    //         selObj: selObj
    //     };
    // }
    // const changeStrongText = () => {
    //     let {range,selObj} = getSelectText();
    //     let postText = $('#js-post-content')[0].innerHTML.toString();
    //     console.log($('#js-post-content').getSelection)
    //     postText = returnHtmlentities(postText);
    //     console.log(postText);
    //     let RegexpF = /<b>/g;
    //     let RegexpS = /<\/b>/g;
    //     let result;
    //     let boldFirst = [];
    //     let boldSecond = [];
    //     while (result = RegexpF.exec(postText)) {
    //         boldFirst.push(result.index);
    //     }
    //     while (result = RegexpS.exec(postText)) {
    //         boldSecond.push(result.index);
    //     }
    //     //Bを入れたい場所がboldF(i)とboldS(i)の間に収まるなら</b> <b>と入れる。F側に越えるなら入れたい場所の後ろのところに<b>を入れて普通に戻す。逆も同じ
    //     // for(let i = 0; i < boldFirst.length(); ++i){

    //     // }
    //     $("#js-post-content").empty();
    //     $("#js-post-content").prepend(postText);
    //     // let newNode = document.createElement('b');
    //     // newNode.innerHTML = selObj.toString();
    //     // range.deleteContents();
    //     // range.insertNode(newNode);
    //     }
    $(document).on('click', '#js-strong', function(){
        changeStrongText();
    });

const getPostContent = ()=>{
    let postText = $('#js-post-content')[0].innerHTML.toString();
    postText = htmlentities(changeTag(returnHtmlentities(postText)));
    var $map = {"postText": postText, "send": "postSend", "sender": userId};
    $.ajax({
        type: 'POST',
        url: './views/component/ajax.php',
        data: $map,
        dataType: 'html'
    }).done(function(data){
        $("#js-posts").prepend(data);
    }).fail(function(msg, XMLHttpRequest, textStatus, errorThrown){
        alert("error: "+msg.responseText);
        console.log(msg);
        console.log(XMLHttpRequest.status);
        console.log(textStatus);
        console.log(errorThrown);
    });
}

function changeTag(str){
    console.log(str);
    return String(str).replace(/<b>/g, "ŠtrŒÑg;")
        .replace(/<\/b>/g,"/ŠtrŒÑg;")
    }

function returnHtmlentities(str){
    return String(str).replace(/&lt;/g,"<")
    // .replace(/&amp;/g,"&")
        .replace(/&gt;/g,">")
    // .replace(/&quot;/g,"\"")
}

function htmlentities(str){
    return String(str).replace(/&/g,"&amp;")
        .replace(/</g,"&lt;")
        .replace(/>/g,"&gt;")
        .replace(/"/g,"&quot;")
}
</script>

<script type="text/javascript"src="../assets/js/websocket.js"></script>

<div class='home-all-contents'>
    <div class=tweet-btn>
        <button id="js-show-popup">ツイートする</button>
    </div>
    <?php if (!empty($_SESSION["userID"])) :?>
    <div class="popup" id="js-popup">
    <div class="popup-inner">
        <div class="close-btn" id="js-close-btn">
            <i class="fas fa-times"></i>
        </div>
        <button
        class="tweet-submit-btn btn"
        name="send"
        form="tweet"
        onclick=" getPostContent(); socketSend();">ツイートする</button>
        <div id="tweet" class="tweet-form">
            <label for="post-content">投稿を入力して下さい</label>
            <div id="js-post-content" class="tweet-textarea"  role="textbox"
            contenteditable="true"
            aria-multiline="true" aria-required="true" aria-autocomplete="list" spellcheck="auto" dir="auto"
            aria-placeholder="投稿を入力して下さい" name="tweet-input">
            </div>
        </div>
        <p class="tweet-items">
                <button type="button" class="tweet-item" id="js-strong" ><i class="fas fa-bold"></i></button>
                <button type="button" class="tweet-item"
                id="js-italic"><i class="fas fa-italic"></i></button>
                <button type="button" class="tweet-item" id="js-underline"><i class="fas fa-underline"></i></button>
                <button type="button" class="tweet-item" id="js-link"><i class="fas fa-link"></i></button>
                <button type="button" class="tweet-item" id="js-paperclip"><i class="fas fa-paperclip"></i></button>
                <button type="button" class="tweet-item" id="js-image"><i class="far fa-image"></i></button>
        </p>
    </div>
    <div class="black-background" id="js-black-bg"></div>
    </div>
    <?php else :?>
    <div class="popup" id="js-popup">
    <div class="popup-inner">
    <div class="close-btn" id="js-close-btn">
        <i class="fas fa-times"></i>
    </div>
        <p class="tweet-not-login">
            ログインしてください。
        </p>
    </div>
    <div class="black-background" id="js-black-bg"></div>
    </div>
    <?php endif;?>
    <div id="js-posts" class="user-posts">
        <!-- <div id="js-posts"></div> -->
            <?php include(__DIR__ . '/component/user_posts.php')?>
    </div>
</div>

<!-- 0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ
私の名前は淀川海都です。\nよろしくお願いします。 -->