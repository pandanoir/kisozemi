<?php

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/php/database.php';
require_logined_session();

header('Content-Type: text/html; charset=UTF-8');
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <style>
    my-user-info, my-search, my-calendar {
        border: 2px solid #888;
        border-radius: 3px;
        padding: 10px;
        margin: 10px 0;
        display: block;
    }
    </style>
  </head>
  <body>
    <my-calendar></my-calendar>
    <my-search></my-search>
    <my-user-info generate-token="<?=h(generate_token())?>"></my-user-info>
    <script src="node_modules/parsimmon/build/parsimmon.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/superagent/2.0.0/superagent.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/riot/2.4.1/riot.min.js"></script>
    <script src="https://npmcdn.com/riotcontrol@0.0.3"></script>
    <script src="js/general.js"></script>
    <script src="js/calendar.js"></script>
    <script src="js/shuntingyard.js"></script>
    <script src="js/parser.js"></script>
    <script src="js/group.js"></script>
    <script src="js/user.js"></script>
    <script src="js/events.js"></script>
    <script src="js/api.js"></script>
    <script src="tags/calendar.js"></script>
    <script src="tags/search.js"></script>
    <script src="tags/groupForm.js"></script>
    <script src="tags/userinfo.js"></script>
    <script>
        var userStore = new User(<?= getUserClassArguments($_SESSION['screen_name']); ?>);
        var eventsStore = new Events();
        var groupsStore = new Groups();
        RiotControl.addStore(userStore);
        RiotControl.addStore(eventsStore);
        RiotControl.addStore(groupsStore);
        riot.mount('*');
    </script>
  </body>
</html>