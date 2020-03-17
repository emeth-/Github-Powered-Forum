# Github-Powered-Forum

Demo: http://githubpoweredforums.herokuapp.com/

Demo's Github Database Repo: https://github.com/emeth-/Github-Powered-Forum-Demo

## What is it?
GPF (Github-Powered-Forum) is a simple forum software written in PHP which uses Github as the database. It's primarily intended for applications which support a "Login with Github" feature - as it requires no special permissions from the Github API other than those already granted via Login with Github (which is access to read a Github user's email address). Posting new threads/comments (as well as thumbs up/down reactions) are supported via little popups to Github. It supports both Github's unauthenticated API (for users not logged in) and Github's authenticated API.

## ...Why?
I have a simple programmer/hacker text-based game I made that requires logging in with Github. The nature of the game required some kind of discussion forums. I look at installing one of the many available open-source solutions, and some paid solutions - all had too much friction for users to start participating (they had to be REALLY interested to go through with it). I needed something that didn't require an additional login, didn't require much programming from me (I'm a one-man shop), and didn't significant increase my attack surface (I have a lot of hackers on my site). I came across [this blog post](http://donw.io/post/github-comments/) of a guy who used Github issues to replace his blog comments, got inspired, and made this project in a weekend that would perfectly match my requirements.

## For new posts/threads, why do you make a popup instead of using the api? (Ditto for reactions and thumbs up/down)
New posts/threads/reactions require the [public_repo permission](https://developer.github.com/apps/building-oauth-apps/understanding-scopes-for-oauth-apps/#available-scopes), which grants full read/write access to everything in a user's public repositories, including their code. Everything else (including the login) I'm able to do with the minimal permission of accessing the user's email address stored on Github. Part of my requirements was reducing friction to get user participation in the forums - requiring them to post in a popup is an increase in friction, but is minimal compared to requiring them to grant me full write access to all the code in their public repositories.

## How to install?
### Setup database repository
- Make a new, empty repo in Github to serve as your forums database.
- Delete all of it's labels, then create labels for each forum you want (e.g. created a "General Discussion" label if you want a forum named "General Discussion").
- Now you need to setup Issue Templates for each label you made. This allows outside users to create new issues in your repo with labels attached to them. To do so, go to your database repo's Settings page, scroll down to "Set up [issue] templates" and click it. Make one issue template for each label you made, attach a label to it at the bottom, and ensure the issue template name is identical to the label name you attach to it.
### Obtain Github API keys
- Go [here](https://github.com/settings/developers) in Github, and create a new OAuth app. Once it's registered, you'll obtain a Client ID and a Client Secret. We'll need those in a minute.
### Setup forums

- To install forums, checkout this library, upload it somewhere, and set the following environmental variables:
- github_client_id = <the Client ID you obtained earlier>
- github_client_secret = <the Client Secret you obtained earlier>
- github_repo_database = <the username/reponame combination where your database repository is located on github. For example, on my [example repo](https://github.com/emeth-/Github-Powered-Forum-Demo), this value is "emeth-/Github-Powered-Forum-Demo"]
- github_labels_on_repo_database = <a JSON list string of all valid labels for your org. On my [example repo](https://github.com/emeth-/Github-Powered-Forum-Demo), the value here is ["General Discussion","Questions & Answers","Tutorials & Guides","Suggestions","Bugs"]>

Click here to automatically deploy it to Heroku (though you'll still need to update the environmental variables):
[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

## How to add an additional forum post-installation?
- In your database repository, you need to add the label for the new forum, and then add an issue template for it as well.
- Then in your deployment, update the json string in the environment variable github_labels_on_repo_database to include the new label.

# TODO:
- Catch rate-limit message for unauthenticated api and display note requiring user to login.
- Add 'view on github' on our master header in this demo.
