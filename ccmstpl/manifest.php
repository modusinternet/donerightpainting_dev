<?
header("Content-Type: application/manifest+json; charset=UTF-8");
header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + ($CFG["CACHE_EXPIRE"] * 60)));
?>{CCMS_DB_PRELOAD:all,index}{
	"short_name": "Done Right Painting",
	"name": "{CCMS_DB:all,company-name}",
	"description": "{CCMS_DB:index,description}",
	"icons": [
		{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-36x36.png",
			"sizes": "36x36",
			"type": "image/png",
			"purpose": "any maskable"
		},{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-48x48.png",
			"sizes": "48x48",
			"type": "image/png",
			"purpose": "any maskable"
		},{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-72x72.png",
			"sizes": "72x72",
			"type": "image/png",
			"purpose": "any maskable"
		},{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-96x96.png",
			"sizes": "96x96",
			"type": "image/png",
			"purpose": "any maskable"
		},{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-144x144.png",
			"sizes": "144x144",
			"type": "image/png",
			"purpose": "any maskable"
		},{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-192x192.png",
			"sizes": "192x192",
			"type": "image/png",
			"purpose": "any maskable"
		},{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-256x256.png",
			"sizes": "256x256",
			"type": "image/png",
			"purpose": "any maskable"
		},{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-384x384.png",
			"sizes": "384x384",
			"type": "image/png",
			"purpose": "any maskable"
		},{
			"src": "{CCMS_LIB:site.php;FUNC:load_resource("AWS")}/ccmstpl/_img/ico/android-chrome-512x512.png",
			"sizes": "512x512",
			"type": "image/png",
			"purpose": "any maskable"
		}
	],
	"start_url": "/",
	"theme_color": "#ffffff",
	"background_color": "#ffffff",
	"display": "standalone",
	"scope": "/",
	"lang": "{CCMS_LIB:_default.php;FUNC:ccms_lng}",
	"dir": "{CCMS_LIB:_default.php;FUNC:ccms_lng_dir}"
}
