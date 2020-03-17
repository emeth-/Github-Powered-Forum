<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Github Powered Forums</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
    body {
      min-height: 2000px;
      padding-top: 70px;
    }
    .tall-row {
        margin-top: 40px;
    }
    .modal {
        position: relative;
        top: auto;
        right: auto;
        left: auto;
        bottom: auto;
        z-index: 1;
        display: block;
    }

  td.details-control {
      background: url('/static/images/details_open.png') no-repeat center center;
      cursor: pointer;
  }
  tr.shown td.details-control {
      background: url('/static/images/details_close.png') no-repeat center center;
  }
  tr.loading td {
      text-align: center;
  }

.progress {
  position: relative;
}

.progress span {
    position: absolute;
    display: block;
    width: 100%;
    color: black;
}

    </style>
    <script src="js/jquery_1_11_2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/moment.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>

    <script>

function urlencode(s) {
    return encodeURIComponent(s);
}

function htmlentities(s) {
    return s.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
       return '&#'+i.charCodeAt(0)+';';
    });
}

function quoteattr(s, preserveCR) {
    preserveCR = preserveCR ? '&#13;' : '\n';
    return ('' + s) /* Forces the conversion to string. */
        .replace(/&/g, '&amp;') /* This MUST be the 1st replacement. */
        .replace(/'/g, '&apos;') /* The 4 other predefined entities, required. */
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        /*
        You may add other replacements here for HTML only
        (but it's not necessary).
        Or for XML, only if the named entities are defined in its DTD.
        */
        .replace(/\r\n/g, preserveCR) /* Must be before the next replacement. */
        .replace(/[\r\n]/g, preserveCR);
        ;
}

    function commanist(number) {
        var num = number.toString();
        if (num) {
            return num.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        return "";
    }

    function getSearchParameters() {
          var prmstr = window.location.search.substr(1);
          return prmstr != null && prmstr != "" ? transformToAssocArray(prmstr) : {};
    }

    function transformToAssocArray( prmstr ) {
        var params = {};
        var prmarr = prmstr.split("&");
        for ( var i = 0; i < prmarr.length; i++) {
            var tmparr = prmarr[i].split("=");
            params[tmparr[0]] = tmparr[1];
        }
        return params;
    }

    var url_params = getSearchParameters();

    $(function () {
      $('[data-toggle="tooltip"]').tooltip();
    })
    </script>
</head>

<body>
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">Github Powered Forums</a>

            <form class="navbar-form navbar-left"> <div class="form-group">
                <a class='btn btn-default' href="https://github.com/emeth-/Github-Powered-Forum" target="_blank">Source Code</a>
            </div>
        </form>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">

            <?php
                if(!isset($_SESSION['github_access_token']) || !$_SESSION['github_access_token']) {
            ?>
            <form class="navbar-form navbar-left"> <div class="form-group">
                <a class='btn btn-info' href="https://github.com/login/oauth/authorize?scope=user:email&client_id=<?=$client_id?>">Login with Github</a>
            </div>
        </form>

            <?php
                }
                else {
            ?>
            <form class="navbar-form navbar-left"> <div class="form-group">
                <a class='btn btn-default' href="#">Welcome, <?=$_SESSION['github_username']?></a>
            </div>
        </form>

            <?php
                }
            ?>
            </ul>
        </div>

    </div>
</nav>
