```
/**
 * Coub 'mylikes' downloader
 * @see http://coub.com/dev/docs/Coub+API/Overview
 * @see https://coub.com/dev/docs/Coub+API/Authentication
 * @see https://coub.com/dev/docs/Coub+API/Timelines
 *
 * 1. First register your application under
 * http://coub.com/dev/applications/
 *
 * 2. Obtain temporary access-token (CODE1) by doing GET request like this (open in browser)
 * http://coub.com/oauth/authorize/?client_id=APP_ID&response_type=code&redirect_uri=http://randomhost.com
 *
 * 3. Obtain actual access-token (CODE2) by doing POST request like this
 * curl -d "grant_type=authorization_code&client_id=APP_ID&redirect_uri=http://randomhost.com&client_secret=APP_SEC&code=CODE1"
 * http://coub.com/oauth/token
 *
 * Example response from Coub API
 * {"access_token":"MYCODE2","token_type":"bearer","expires_in":31104000,"scope":"logged_in","created_at":1499120437}
 *
 * 4. use MYCODE2 for further API requests, together with your channel-id
 *
 * $ php fetch.php MYCHANNELID MYCODE2
 */
```

Json CLI parser
https://stedolan.github.io/jq/tutorial/
