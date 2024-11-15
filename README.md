Tag Denton 
========== 
**Tag Denton** is a simple web tool that creates short URLs for social posts associated with landmarks in Denton, Texas. The generated URLs are designed to work with NFC tags, allowing users to scan the tags and view relevant social posts directly in the app. 

Features 
-------- 
* **Short URL Generation**: Paste a URL, and Tag Denton creates a short URL you can use with NFC tags.
* **Automatic App Redirection**: Attempts to open the native app directly, with a fallback to the web version if the app isn’t installed.
 
How It Works 
------------ 
1. Users paste a URL into the input box on the homepage. 
2. The JavaScript on the page extracts the post ID and generates a custom short URL (e.g., `https://tagdenton.com/redirect/KEY`). 
3. When a user visits the short URL, the PHP script attempts to open the native app with the specified post. 
