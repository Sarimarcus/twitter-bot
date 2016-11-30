# Twitter BOT in Laravel 5 / PHP / MySQL

This is a Twitter Bot, doing a lot of smart things :

- Each 10 minutes : following accounts from configured accounts
- Each 30 minutes : tweet or retweet something interesting, depending of followed accounts and stored tweets
- Each hours : unfollowing accounts i've been following
- Twice a day : retrieve tweets from a search with hashtags
- Each weekdays at 10:00 : tweet an inspiring quote from https://theysaidso.com
- Each weekdays at 14:00 : retweeting a trending tweet of configured city
- Daily a midnight : get suggested users, purge useless users & updating bot stats

It can also :
- Generate poem based on tweets (yes my dear)
- Get some QOTD