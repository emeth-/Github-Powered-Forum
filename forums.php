<?php
include("forums_config.php");
$forum = urldecode($_GET['forum']);

if (!in_array($forum, $labels_on_repo_database)) {
    $forum = "";
}

include("master_header.php");

?>
<style>
</style>
    <script>
<?php

$current_page = intval($_GET['page']);
if(!$current_page) {
    $current_page = 1;
}
$get_threads_url = 'https://api.github.com/search/issues?q=repo:'.$repo_database.'+is%3Aopen+label%3A"'.urlencode($forum).'"&sort=updated&order=desc';
$get_thread_url = "https://api.github.com/repos/".$repo_database."/issues/[thread_id]";
$get_thread_comments_url = "https://api.github.com/repos/".$repo_database."/issues/[thread_id]/comments?per_page=25&page=".$current_page;
$get_recent_active_threads_url = 'https://api.github.com/search/issues?q=repo:'.$repo_database.'+is%3Aopen&sort=updated&order=desc';
$search_url = 'https://api.github.com/search/issues?q=[query]+repo:'.$repo_database.'+is%3Aopen&sort=updated&order=desc';

if(isset($_SESSION['github_access_token']) && $_SESSION['github_access_token']) {
    $get_threads_url = "forums_api.php?act=get_threads&forum=".urlencode($forum);
    $get_thread_url = "forums_api.php?act=get_thread&thread_id=[thread_id]";
    $get_thread_comments_url = "forums_api.php?act=get_thread_comments&thread_id=[thread_id]&page=".$current_page;
    $get_recent_active_threads_url = "forums_api.php?act=get_recent_active_threads";
    $search_url = "forums_api.php?act=search&q=[query]";
}
?>
var forums = <?=json_encode($labels_on_repo_database)?>;
var get_threads_url = '<?=$get_threads_url?>';
var get_thread_url = '<?=$get_thread_url?>';
var get_thread_comments_url = '<?=$get_thread_comments_url?>';
var get_recent_active_threads_url = '<?=$get_recent_active_threads_url?>';
var search_url = '<?=$search_url?>';
var current_page = <?=$current_page?>;

function get_forums() {
    var htmlz = `
    <table class="table table-striped table-hover ">
        <tbody>`;
    for (var i=0; i<forums.length; i++) {
        htmlz += `
            <tr>
                <td><a href="?forum=`+urlencode(forums[i])+`" style='font-size:20px'>`+forums[i]+`</a></td>
            </tr>
        `;
    }
    htmlz += `
        </tbody>
        </table>
        <br><hr><br>
        <div id='recent_active_threads'></div>
    `;
    $("#gh-comments-list").html(htmlz);

    //last_5_posts
    $.ajax(get_recent_active_threads_url, {
        headers: {
            Accept: "application/vnd.github.squirrel-girl-preview"
        },
        dataType: "json",
        success: function(threads) {
            var htmlz = `<table class="table table-striped table-hover ">
                <thead>
                    <tr>
                        <th style="font-size: 20px">Recently Active Threads</th>
                        <th>Replies</th>
                        <th>Starter</th>
                        <th>Last Post</th>
                    </tr>
                </thead>
                <tbody>`;

            $.each(threads.items, function(i, thread) {
                if(i < 5) {
                    var date_created = new Date(thread.created_at);
                    var date_last_post = new Date(thread.updated_at);
                    if(thread.labels.length > 0) {
                        htmlz += `
                            <tr>
                                <td><a href="?forum=`+urlencode(thread.labels[0].name)+`&thread=${thread.number}" title="`+htmlentities(thread.body)+`">${thread.title}</a></td>
                                <td>`+commanist(thread.comments)+`</td>
                                <td>${thread.user.login} (`+moment(date_created).fromNow()+`)</td>
                                <td>`+moment(date_last_post).fromNow()+`</td>
                            </tr>
                        `;
                    }
                }
            });

            htmlz += `
        </tbody>
    </table>
            `;

            $("#recent_active_threads").html(htmlz);
        },
        error: function() {
            $("#recent_active_threads").html("");
        }
    });
}

function get_threads() {
    $.ajax(get_threads_url, {
        headers: {
            Accept: "application/vnd.github.squirrel-girl-preview"
        },
        dataType: "json",
        success: function(threads) {
            var htmlz = `<table class="table table-striped table-hover ">
                <thead>
                    <tr>
                        <th>Threads</th>
                        <th>Replies</th>
                        <th>Starter</th>
                        <th>Last Post</th>
                    </tr>
                </thead>
                <tbody>`;

            $.each(threads.items, function(i, thread) {

                var date_created = new Date(thread.created_at);
                var date_last_post = new Date(thread.updated_at);

                htmlz += `
                    <tr>
                        <td><a href="?forum=<?=urlencode($forum)?>&thread=${thread.number}" title="`+htmlentities(thread.body)+`">${thread.title}</a></td>
                        <td>`+commanist(thread.comments)+`</td>
                        <td>${thread.user.login} (`+moment(date_created).fromNow()+`)</td>
                        <td>`+moment(date_last_post).fromNow()+`</td>
                    </tr>
                `;
            });

            htmlz += `
        </tbody>
    </table>
            `;

            $("#gh-comments-list").html(htmlz);
        },
        error: function() {
            $("#gh-comments-list").html("Error.");
        }
    });
}

function get_thread(thread_id) {
    thread_id = parseInt(thread_id);
    var htmlz = `
        <div id='threadheader_div'>Loading...</div>
        <div id='replies_div'></div>
    `;
    $("#gh-comments-list").html(htmlz);
    get_thread_header(thread_id);
    get_thread_replies(thread_id);
}

var number_of_pages = 1;
function get_thread_header(thread_id) {
    $.ajax(get_thread_url.replace("[thread_id]", thread_id), {
        headers: {
            Accept: "application/vnd.github.v3.html+json, application/vnd.github.squirrel-girl-preview"
        },
        dataType: "json",
        success: function(thread) {
            var htmlz = ``;
            var date = new Date(thread.created_at);


            number_of_pages = parseInt(parseFloat(thread.comments)/parseFloat(25));
            if((thread.comments % 25) > 0) {
                number_of_pages += 1;
            }
            if(!number_of_pages) {
                number_of_pages = 1;
            }

            var previous_page = current_page - 1;
            var previous_url = `?forum=<?=urlencode($forum)?>&thread=${thread.number}&page=${previous_page}`;
            var previous_disabled = "";
            if(current_page == 1) {
                previous_disabled = " class='disabled'";
                previous_url = "#";
            }

            var next_page = current_page + 1;
            var next_url = `?forum=<?=urlencode($forum)?>&thread=${thread.number}&page=${next_page}`;
            var next_disabled = "";
            if(current_page == number_of_pages) {
                next_disabled = " class='disabled'";
                next_url = "#";
            }
            htmlz += `
            <table style="width:100%;">
            <tr>
            <td style="width:50%;">
                <h3>${thread.title}</h3>
            </td>
            <td style="width:50%;text-align:right">
<nav>
  <ul class="pager" style="text-align:right">
    <li ${previous_disabled}><a href="${previous_url}">Previous</a></li>
    <li class='disabled'>Page ${current_page} of ${number_of_pages}</li>
    <li ${next_disabled}><a href="${next_url}">Next</a></li>
  </ul>
</nav>
            </td>
            </tr>
            </table>
            `;
            if(current_page == 1) {
                //only display the thread initial post on page 1
                htmlz += `
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <b style='color: #0c0;'>${thread.user.login}</b>
                            posted
                            <em>`+moment(date).fromNow()+`</em>
    <span style="float:right;">
    <button class="btn btn-default" type="button"  onclick='open_popup_thumbs_thread(`+thread_id+`, `+thread.id+`);return false;'>
      <span class="glyphicon glyphicon-thumbs-up"></span> <span class="badge">${thread.reactions['+1']}</span>
    </button>

    <button class="btn btn-default" type="button"  onclick='open_popup_thumbs_thread(`+thread_id+`, `+thread.id+`);return false;'>
      <span class="glyphicon glyphicon-thumbs-down"></span> <span class="badge">${thread.reactions['-1']}</span>
    </button>
    </span>
                        </div>
                        <div class="panel-footer">${thread.body_html}</div>
                    </div>
                `;
            }

            $("#threadheader_div").html(htmlz);
        },
        error: function() {
            $("#threadheader_div").html("Error.");
        }
    });
}

function get_thread_replies(thread_id) {
    $.ajax(get_thread_comments_url.replace("[thread_id]", thread_id), {
        headers: {
            Accept: "application/vnd.github.v3.html+json, application/vnd.github.squirrel-girl-preview"
        },
        dataType: "json",
        success: function(comments) {
            var htmlz = ``;
            console.log("sb1", comments);

            $.each(comments, function(i, comment) {

                var date2 = new Date(comment.created_at);

                htmlz += `
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <b style='color: #0c0;'>${comment.user.login}</b>
                            posted
                            <em>`+moment(date2).fromNow()+`</em>
<span style="float:right;">
<button class="btn btn-default" type="button" onclick='open_popup_thumbs_comment(`+thread_id+`, `+comment.id+`);return false;'>
<span class="glyphicon glyphicon-thumbs-up"></span> <span class="badge">${comment.reactions['+1']}</span>
</button>

<button class="btn btn-default" type="button" onclick='open_popup_thumbs_comment(`+thread_id+`, `+comment.id+`);return false;'>
<span class="glyphicon glyphicon-thumbs-down"></span> <span class="badge">${comment.reactions['-1']}</span>
</button>
</span>
                        </div>
                        <div class="panel-footer">${comment.body_html}</div>
                    </div>
                `;
            });
            htmlz += `
            <div class='well'>
<center><a href="#" class="btn btn-primary" target='_blank' onclick='open_popup_new_comment(`+thread_id+`);return false;'>Create Reply</a></center>
            </div>
            `;

            $("#replies_div").html(htmlz);
        },
        error: function() {
            $("#replies_div").html("");
        }
    });
}

var thumbsthread_popup;
var thumbsthread_popup_polltimer;
function open_popup_thumbs_thread(thread_id, message_id) {
    thumbsthread_popup = window.open('https://github.com/<?=$repo_database?>/issues/'+thread_id+'#issue-'+message_id, 'Rate', 'status=1, height=800, width=1000, left=100, top=100, resizable=0');

    thumbsthread_popup_polltimer = window.setInterval(function() {
        if (thumbsthread_popup.closed !== false) {
            window.clearInterval(thumbsthread_popup_polltimer);
            get_thread_header(thread_id);
        }
    }, 200);
}

var thumbscomment_popup;
var thumbscomment_popup_polltimer;
function open_popup_thumbs_comment(thread_id, message_id) {
    thumbscomment_popup = window.open('https://github.com/<?=$repo_database?>/issues/'+thread_id+'#issuecomment-'+message_id, 'Rate', 'status=1, height=800, width=1000, left=100, top=100, resizable=0');

    thumbscomment_popup_polltimer = window.setInterval(function() {
        if (thumbscomment_popup.closed !== false) {
            window.clearInterval(thumbscomment_popup_polltimer);
            get_thread_replies(thread_id);
        }
    }, 200);
}

var reply_popup;
var reply_popup_polltimer;
function open_popup_new_comment(thread_id) {
    reply_popup = window.open('https://github.com/<?=$repo_database?>/issues/'+thread_id+'#new_comment_field', 'Add Comment', 'status=1, height=800, width=1000, left=100, top=100, resizable=0');

    reply_popup_polltimer = window.setInterval(function() {
        if (reply_popup.closed !== false) {
            window.clearInterval(reply_popup_polltimer);
            get_thread_replies(thread_id);
        }
    }, 200);
}

var newthread_popup;
var newthread_popup_polltimer;
function open_popup_new_thread(thread_id) {
    newthread_popup = window.open('https://github.com/<?=$repo_database?>/issues/new?assignees=&labels=<?=urlencode($forum)?>&template=<?=strtolower(str_replace("&", "-", str_replace(" ", "-", $forum)))?>.md&title=', 'Add Thread', 'status=1, height=800, width=1000, left=100, top=100, resizable=0');

    newthread_popup_polltimer = window.setInterval(function() {
        if (newthread_popup.closed !== false) {
            window.clearInterval(newthread_popup_polltimer);
            get_threads();
        }
    }, 200);

}



$(document).ready(function() {

    if(url_params['search']) {
        render_search_results(url_params['search']);
    }
    else if(url_params['thread']) {
        get_thread(url_params['thread']);
    }
    else if("<?=$forum?>"){
        get_threads();
    }
    else {
        get_forums();
    }
} );

    </script>
    <style>
    </style>

    <div class="container">

<div class="row">
    <div class="col-md-7">
        <h1><a href='?'>Forums</a>
            <?php
            if($forum) {
            ?>
            &gt; <a href='?forum=<?=urlencode($forum)?>'><?=$forum?></a>
            <?php
            }

            if($_GET['search']) {
            ?>
            &gt; Search
            <?php
            }
            ?>
        </h1>
    </div>
    <div class="col-md-2" style="">&nbsp;
    <?php
    if(!isset($_GET['thread']) && $forum) {
    ?>
        <a href="#" class="btn btn-primary" target='_blank' style='margin-top: 20px;float:right;' onclick='open_popup_new_thread();return false;'>Create Thread</a>

    <?php
    }
    ?>

    </div>
    <div class="col-md-3" style="">
        <div class="form-group" style='margin-top: 20px;float:right;'>
            <div class="input-group">


                <input class="form-control" type="text" placeholder='Search Query...' id='search_query' value="<?=htmlentities($_GET['search'])?>">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button" onclick='submit_search()' id='search_button' style='border: 1px solid #444;'>Search</button>
                </span>

                <span class="input-group-addon"><a href='https://help.github.com/en/github/searching-for-information-on-github/searching-issues-and-pull-requests#search-by-a-user-thats-involved-in-an-issue-or-pull-request' target='_blank'><span class="glyphicon glyphicon-question-sign typetooltip" data-placement="bottom" data-toggle="tooltip" data-placement="right" title="" data-original-title="Click for search qualifiers"></span></a></span>
            </div>
        </div>
    </div>

    <script>
    function render_search_results(q) {
        $.ajax(search_url.replace("[query]", q), {
            headers: {
                Accept: "application/vnd.github.v3.text-match+json, application/vnd.github.squirrel-girl-preview"
            },
            dataType: "json",
            success: function(search_results) {
                var htmlz = ``;

                $.each(search_results.items, function(i, r) {
                    if(r.labels.length > 0) {
                        var date2 = new Date(r.updated_at);
                        var display_text = r.body;
                        if(r.text_matches.length > 0) {
                            display_text = "[...] "+r.text_matches[0].fragment+" [...]";
                        }
                        htmlz += `
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h3 class="panel-title" style="display: inline"><a href='/forums.php?forum=`+urlencode(r.labels[0].name)+`'>`+r.labels[0].name+`</a> &gt; <a href='/forums.php?forum=`+urlencode(r.labels[0].name)+`&thread=${r.number}' style='text-decoration: underline;'>${r.title}</a>

                                    <span style='color:white'> (thread by <b style='color: #0c0;'>${r.user.login}</b>)
                                    </span>
                                    </h3>
                                    <span style="float:right;color: white;">(Last Post: `+moment(date2).fromNow()+`)</span>
                                </div>
                                <div class="panel-body">
                                    `+display_text+`
                                </div>
                            </div>
                        `;
                    }
                });
                htmlz += `
                `;

                $("#gh-comments-list").html(htmlz);
            },
            error: function() {
                $("#gh-comments-list").html("");
            }
        });
    }

function submit_search() {
    window.location='forums.php?search='+$('#search_query').val();
}

document.getElementById("search_query").addEventListener("keyup", function(event) {
      if (event.keyCode === 13) {
        event.preventDefault();
        document.getElementById("search_button").click();
      }
});
    </script>
</div>
<hr>

<div id='gh-comments-list'></div>
    </div>


</body>
</html>
