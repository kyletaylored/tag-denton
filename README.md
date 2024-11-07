Tag Denton 
========== 
**Tag Denton** is a simple web tool that creates short URLs for Instagram posts associated with landmarks in Denton, Texas. The generated URLs are designed to work with NFC tags, allowing users to scan the tags and view relevant Instagram posts directly in the app. 

Features 
-------- 
* **Short URL Generation**: Paste an Instagram post URL, and Tag Denton creates a short URL you can use with NFC tags.
* **Automatic App Redirection**: Attempts to open the Instagram app directly, with a fallback to the Instagram web version if the app isn’t installed.
 
How It Works 
------------ 
1. Users paste an Instagram post URL into the input box on the homepage. 
2. The JavaScript on the page extracts the post ID and generates a custom short URL (e.g., `https://tagdenton.com/redirect.php?id=POST_ID`). 
3. When a user visits the short URL, the PHP script (`redirect.php`) attempts to open the Instagram app with the specified post. 

Installation 
------------ 
### Prerequisites 
To host Tag Denton locally or on a server, you’ll need: 
* PHP-enabled hosting (required for the `redirect.php` script). 
* Optional: GitHub Pages or Netlify for hosting the static portion (JavaScript and HTML) of the site if PHP hosting is separate. 

### Steps 
1. **Clone the Repository**: bash Copy code `git clone https://github.com/kyletaylored/tag-denton.git cd tag-denton` 
2. **Deploy to a Web Server**: 
  * If using a single PHP server, upload all files to the server’s root directory. 
  * If hosting the HTML on a static server (e.g., GitHub Pages), upload only `index.html` and remove or separately host `redirect.php`. 
3. **Access the Site**: 
  * Open `index.html` in a browser to view the homepage and generate custom short URLs. 
  * Use the format `https://tagdenton.com/redirect.php?id=POST_ID` to test redirections. 

Usage 
----- 
1. Go to the homepage (`index.html`). 
2. Paste an Instagram post URL into the input box. 
3. Click “Generate Link” to see the custom Tag Denton URL. 
4. Copy the generated link and use it with NFC tags or share directly. 

Contributing 
------------ 
If you'd like to contribute to **Tag Denton**, feel free to submit a pull request with new features or bug fixes.
