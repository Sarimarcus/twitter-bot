# Twitter BOT in Laravel PHP (i know, it's not that special, but I am)

This is a Twitter BOT, doing a lot of smart things :

- Each 30 minutes : tweet or retweet something interesting, depending of followed accounts, follow users of those accounts and stored tweets
- Each weekdays at 14:00 : retweeting a trending tweet in Paris
- Each friday at 16:00 : tweet an inspiring quote
- Daily a midnight : get suggested users, purge useless users, unfollow users
- Twice a day : retrieve tweets from a search with hashtags
