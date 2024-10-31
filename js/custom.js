// Get all data related to which post type or taxonomy has the priority to be shown in app home screen
const allPostTypes = document.querySelectorAll(".checkpt");
const allTaxonomies = document.querySelectorAll(".checktax");
let hasPriority;

// Function to disable the priority checkbox of other post types if a post type is checked
function adjustPriority(i) {
	const targetClass = document.querySelector(`#prior_${i}`).className;
	hasPriority = document.querySelector(`#prior_${i}`).checked;
	const start = +document.querySelector(`#prior_${i}`).dataset.start;
	const end =
		targetClass == "priortax"
			? allTaxonomies.length + start
			: allPostTypes.length;

	if (hasPriority) {
		for (let j = start; j < end; j++) {
			if (j != i) {
				document.querySelector(`#prior_${j}`).checked = false;
				document.querySelector(`#prior_${j}`).disabled = true;
			}
		}
	} else {
		for (let j = start; j < end; j++) {
			if (j != i) {
				document.querySelector(`#prior_${j}`).disabled = false;
			}
		}
	}
}

document
	.getElementById("dropbtnposts")
	.addEventListener("click", function (event) {
		event.preventDefault();

		document.getElementById("myDropdownPosts").classList.toggle("show");
		document.getElementById("myDropdownTaxes").classList.remove("show");
	});

document
	.getElementById("dropbtntaxes")
	.addEventListener("click", function (event) {
		event.preventDefault();
		document.getElementById("myDropdownTaxes").classList.toggle("show");
		document.getElementById("myDropdownPosts").classList.remove("show");
	});

function filterPosts(name) {
	var input, filter, a, i;
	input = document.getElementById("myInput" + name);
	filter = input.value.toUpperCase();
	div = document.getElementById("myDropdown" + name);
	a = div.getElementsByTagName("a");
	for (i = 0; i < a.length; i++) {
		txtValue = a[i].textContent || a[i].innerText;
		if (txtValue.toUpperCase().indexOf(filter) > -1) {
			a[i].style.display = "";
		} else {
			a[i].style.display = "none";
		}
	}
}

//choose a post to be excluded from the drop down input
function addExcludedPost(id) {
	const target = document.querySelector(`#postexl${id}`);
	exclude(id, target);
}
//choose a taxonomy to be excluded from the drop down input
function addExcludedTax(id) {
	const target = document.querySelector(`#taxexcl${id}`);
	exclude(id, target);
}

//Show excluded posts/taxonomies in the plugin
function exclude(id, target) {
	// console.log(target);
	var excludedPostsId = document.getElementById(
		`excluded_${target.dataset.type}`,
	);
	excludedPostsId.value += id + ",";
	var excludedPostsName = document.getElementById(
		`excluded_${target.dataset.type}_name`,
	);
	excludedPostsName.innerHTML +=
		'<span class="w3-tag w3-red w3-round w3-margin">' +
		target.textContent +
		' <span id="dismiss-' +
		id +
		'" class="dashicons dashicons-dismiss" onclick="excludeDismiss(' +
		id +
		", " +
		"'" +
		target.dataset.type +
		"'" +
		')"></span></span>';
}

//Reinclude (cancel excluding) a post or a taxonomy
function excludeDismiss(id, type) {
	var excludedPost = document.getElementById("dismiss-" + id);
	var excludedPostsId = document.getElementById(`excluded_${type}`);
	excludedPost.parentNode.parentNode.removeChild(excludedPost.parentNode);
	excludedPostsId.value = excludedPostsId.value.replace(id + ",", "");
}

// Get chosen post types and taxonomies
function prepareObject(length, type) {
	let posts = [];
	let isChecked;
	let name;
	let endPoint;
	let postType;
	const [taxPriorObject] = document.querySelectorAll(`.priortax`);
	const priorStart = type == "post" ? 0 : +taxPriorObject.dataset.start;

	for (let i = 0; i < length; i++) {
		console.log(`check${type}${i}`);
		isChecked = document.querySelector(`#check${type}${i}`).checked;
		endPoint = document.querySelector(`#check${type}${i}`).name;
		name = document.querySelector(`#${type}${i}`).value;
		hasPriority = document.querySelector(`#prior_${i + priorStart}`).checked;

		postTypeArray =
			type == "tax" ? document.querySelectorAll(`.related_types`) : null;

		postTypeArray =
			type == "tax" ? Array.prototype.slice.call(postTypeArray) : null;

		postType = type == "tax" ? postTypeArray[i].value : null;
		if (isChecked) {
			posts[i] =
				type == "tax"
					? { name, endPoint, postType, hasPriority }
					: { name, endPoint, hasPriority };
		}
	}

	posts.sort((a, b) => (b.hasPriority ? 1 : -1));

	let object = `[`;
	posts.map((post, index) => {
		object += `               {name: '${post.name}', 
                  endPoint: '${post.endPoint}', ${
			type == "tax" ? `postType: "${post.postType}", ` : ""
		}
                },
  `;
	});
	object += `]`;
	return object;
}
// Get excluded post ids
function prepareExcluded(type) {
	const excludedPosts = document.querySelector(`#${type}`).value;
	const excludedPostsObject = "[" + excludedPosts + "]";
	return excludedPostsObject;
}
// Get app settings to be shown in the userConfig object
const url = document.querySelector("#url").value;
const postsObject = prepareObject(allPostTypes.length, "post");
const taxObject = prepareObject(allTaxonomies.length, "tax");
const excludedPostsObject = prepareExcluded("excluded_posts");
const excludedTaxesObject = prepareExcluded("excluded_taxes");
const offlinePerPage = document.querySelector("#offline_per_page").value;
const onlinePerPage = document.querySelector("#online_per_page").value;
const postsFirst =
	document.querySelector("#home_page").value == "posts" ? "true" : "false";

const aboutID = document.querySelector("#about_page").value;
let aboutChildren = document.querySelector("#about_page");
aboutChildren = Array.prototype.slice.call(aboutChildren);
const [chosenChild] = aboutChildren.filter((child) => child.value == aboutID);

const aboutEndPoint = chosenChild.dataset.endpoint;

const showExcerpt = document.querySelector("#show_excerpt").checked;
const maxChar = document.querySelector("#excerpt_max_char").value;

const enableFirebase = document.querySelector("#enable_firebase").checked;

const enableBannerAds = document.querySelector("#enable_banner_ads").checked;
const bannerAdsKey = document.querySelector("#banner_ads_key").value;

const enableInterstitialAds = document.querySelector("#enable_interstitial_ads")
	.checked;
const interstitialAdsKey = document.querySelector("#interstitial_ads_key")
	.value;

const enableRewardedAds = document.querySelector("#enable_rewarded_ads")
	.checked;
const rewardedAdsKey = document.querySelector("#rewarded_ads_key").value;

const isFeaturedImageEnabled = document.querySelector("#enable_featured_image")
	.checked;

const isFAInstalled = document.querySelector("#enable_fa_installed").checked;

const enableScheduled = document.querySelector("#scheduled_enabled").checked;
const notiFrequency = document.querySelector("#notification_frequency").value;
const notiTitle = document.querySelector("#notification_title").value;
const notiMessage = document.querySelector("#notification_message").value;

const textDirection = document.querySelector("#text_direction").value;
const phone = document.querySelector("#phone").value;
const address = document.querySelector("#address").value;
const mail = document.querySelector("#mail").value;
let allSocial = document.getElementsByClassName("social");
allSocial = Array.prototype.slice.call(allSocial);

let socialObject = `[`;
allSocial.map((icon, index) => {
	if (icon.value != "") {
		socialObject += `{icon: '${icon.name}', 
                  link: '${
										icon.name == "whatsapp"
											? `https://api.whatsapp.com/send/?phone=${icon.value}&text&app_absent=0`
											: icon.value
									}', },
  `;
	}
});
socialObject += `]`;

// Get settings related to screen texts and chosen colors

const homeScreenName = document.querySelector("#home_screen_name").value;
const contactScreenName = document.querySelector("#contact_screen_name").value;
const aboutScreenName = document.querySelector("#about_screen_name").value;
const settingScreenName = document.querySelector("#setting_screen_name").value;
const savedScreenName = document.querySelector("#saved_screen_name").value;
const searchScreenName = document.querySelector("#search_screen_name").value;
const noInternet = document.querySelector("#no_internet").value;
const onlineSearch = document.querySelector("#online_search").value;
const noResults = document.querySelector("#no_results").value;
const enableDarkMode = document.querySelector("#enable_dark_mode").value;
const enableNotifications = document.querySelector("#enable_notifications")
	.value;
const enableOffline = document.querySelector("#saved_offline").value;
const checkUpdates = document.querySelector("#check_updates").value;
const downloadingText = document.querySelector("#downloading_text").value;
const doneText = document.querySelector("#done_text").value;
const seeProductPage = document.querySelector("#see_product_page").value;
const downloadAlertTitle = document.querySelector("#download_alert_title")
	.value;
const downloadAlertMessage = document.querySelector("#download_alert_message")
	.value;
const deleteAlertTitle = document.querySelector("#delete_alert_title").value;
const deleteAlertMessage = document.querySelector("#delete_alert_message")
	.value;
const alertYes = document.querySelector("#alert_yes").value;
const alertNo = document.querySelector("#alert_no").value;

const backLight = document.querySelector("#background_light").value;
const textLight = document.querySelector("#text_light").value;
const textAltLight = document.querySelector("#text_alt_light").value;
const containerLight = document.querySelector("#container_light").value;
const switchThumbLight = document.querySelector("#switch_thumb_light").value;
const switchOnLight = document.querySelector("#switch_on_light").value;
const switchOffLight = document.querySelector("#switch_off_light").value;

const backDark = document.querySelector("#background_dark").value;
const textDark = document.querySelector("#text_dark").value;
const textAltDark = document.querySelector("#text_alt_dark").value;
const containerDark = document.querySelector("#container_dark").value;
const switchThumbDark = document.querySelector("#switch_thumb_dark").value;
const switchOnDark = document.querySelector("#switch_on_dark").value;
const switchOffDark = document.querySelector("#switch_off_dark").value;

// Get the object to be copied to the app
const object = `export default userConfig = {
    siteURL: '${url}',
    postTypes: ${postsObject},
    taxonomies: ${taxObject},
    excludedPostIDs: ${excludedPostsObject},
    excludedCatIDs: ${excludedTaxesObject},
    offlinePostsPerPage: ${offlinePerPage},
    onlinePostsPerPage: ${onlinePerPage},
    offlineCatsPerPage: ${offlinePerPage},
    onlineCatsPerPage: ${onlinePerPage},
    searchPerPage: ${+onlinePerPage},
    numberOfPostsToDownload: ${offlinePerPage},
    postsFirst: ${postsFirst},
    pluginInstalled: true,
    aboutPage: { postType: '${aboutEndPoint}', id: ${aboutID} },
    searchPostType: 'posts',
    supportsExcerpt: ${showExcerpt},
	  excerptMaxChar: ${maxChar},
    supportsFeaturedImage: ${isFeaturedImageEnabled},
    enableFirebase: ${enableFirebase},
    enableBannerAds: ${enableBannerAds},
    bannerAdsKey: '${bannerAdsKey}',
    enableInterstitialAds: ${enableInterstitialAds},
    interstitialAdsKey: '${interstitialAdsKey}',
    enableRewardedAds: ${enableRewardedAds},
    rewardedAdsKey: '${rewardedAdsKey}',
    pnPluginInstalled: ${isFAInstalled},
	  enableScheduled: ${enableScheduled},
    scheduledNotificationFrequency: ${notiFrequency},
    textDirection: '${textDirection}',
    contact: {
      telephone: {
        string: "Telephone No.:", value: '${phone}'
      },
      address: {
        string: "Address:", value: "${address}"
      },
      mail: {
        string: "Email:", value: '${mail}'
      },
      social: ${socialObject},
    }, 
    lightColors: {
      background: "${backLight}",
      text: "${textLight}",
      textAlt: "${textAltLight}",
      containerColor: "${containerLight}",
      thumbColor: "${switchThumbLight}",
      trueSwitchColor: "${switchOnLight}",
      falseSwitchColor: "${switchOffLight}",
    },
    darkColors: {
      background: "${backDark}",
      text: "${textDark}",
      textAlt: "${textAltDark}",
      containerColor: "${containerDark}",
      thumbColor: "${switchThumbDark}",
      trueSwitchColor: "${switchOnDark}",
      falseSwitchColor: "${switchOffDark}",
    },
    appTexts: {
		scheduledNotificationTitle: "${notiTitle}",
    scheduledNotificationBody: "${notiMessage}",
		homeScreenName: "${homeScreenName}",
		savedScreenName: "${savedScreenName}",
		contactScreenName: "${contactScreenName}",
		aboutScreenName: "${aboutScreenName}",
		searchScreenName: "${searchScreenName}",
    settingScreenName: "${settingScreenName}",
		noInternet: "${noInternet}",
		onlineSearch: "${onlineSearch}",
		noResults: "${noResults}",
		darkSetting: "${enableDarkMode}",
		notificationSetting: "${enableNotifications}",
		offlineSetting: "${enableOffline}",
		checkUpdatesBtn: "${checkUpdates}",
		downloadingStatement: "${downloadingText}",
		doneStatement: "${doneText}",
		seeProductPage: "${seeProductPage}",
		deleteDataAlert: {
			title: "${deleteAlertTitle}",
			message: "${deleteAlertMessage}",
		},
		downloadDataAlert: {
			title: "${downloadAlertTitle}",
			message: "${downloadAlertMessage}",
		},
		alertPrompt: {
			yes: "${alertYes}",
			no: "${alertNo}",
		},
    }
  }`;

document.getElementById("object").value = object;

document
	.getElementById("code-container")
	.addEventListener("click", function () {
		var copyText = document.getElementById("object");

		copyText.select();
		copyText.setSelectionRange(0, 99999); /*For mobile devices*/

		/* Copy the text inside the text field */
		document.execCommand("copy");
		alert("Text copied to clipboard");
	});
