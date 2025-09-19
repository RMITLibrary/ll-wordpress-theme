// START Script to show and hide hamburger menu
(function() {
    // Grab both the menu button, the page content and footer
    var menuButton = document.getElementById("menu-button");
    var pageContent = document.getElementById("theme-main"); 
    var footer = document.getElementById("wrapper-footer-colophon"); 

    // Store display status of menu
    var menuDisplay = false;

    // Store content scroll position
    var scrollPosition;

    // Listen for click - call showHideMenu
    menuButton.addEventListener("click", showHideMenu);

    function showHideMenu() {
        if (menuDisplay == false) {
            menuDisplay = true;
            scrollPosition = window.scrollY;
            pageContent.style.display = "none"; 
            footer.style.display = "none"; 
        } else {
            menuDisplay = false;
            pageContent.style.display = "block"; 
            footer.style.display = "block"; 
            document.documentElement.style.scrollBehavior = "auto";
            window.scroll(0, scrollPosition);
            document.documentElement.style.scrollBehavior = "smooth";
        }
    }
})();
// END Script to show and hide hamburger menu
    

// START Script to turn on embed mode

// Removes top nav, footer, breadcrumbs, right nav, keywords and prev/next by default
// Options to remove h1, p lead and show prev/next
// Sample query string:
// ?iframe=true&hide-title=true&hide-intro=true
 
// iframe - set to true to enable embed mode
// hide-title - set to true to hide title
// hide-intro - set to true to hid first <p class="lead">
// show-prev-next - set to true to show previous and next buttons
 
// Unlikely hide-title and hide-intro would be used in concert with show-prev-next 

(function() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const embedBool = urlParams.get('iframe');
    const hideTitle = urlParams.get('hide-title');
    const hideIntro = urlParams.get('hide-intro');
    const showPrevNext = urlParams.get('show-prev-next');

    if (embedBool == 'true') {
        embedThisPage();
        handleEmbedLinks();
    }

    // embedThisPage hides or removes markup to optimise the display of a page inside an iframe,
    // depending on additional query string values
    function embedThisPage() {
        // pick up the relevant objects in the page
        var nav = document.querySelector('header');
        var breadcrumbs = document.querySelector('nav[aria-label="breadcrumbs"]');
        var rightNav = document.querySelector('div.col-xl-4.order-last');
        var myFooter = document.getElementById("wrapper-footer-colophon"); 
        
        //These are layout divs, looking to remove bootstrap styling to go full width
        var containerDiv = document.getElementById("page-content");
        var contentDiv = document.querySelector('div.order-first');
        
        //grab content below prev/next buttons
        var additionalInfo = document.getElementById('additional-info');
        
        //grab landing banner for landing pages
        var landingBanner = document.querySelector("div.landing-banner"); 

        // hide nav and footer (we have footer var from script above)
        nav.style.display = "none";
        myFooter.style.display = "none";

        //If breadcrumbs, right nav, additional info or landing banner exists, hide them
        if (breadcrumbs) { breadcrumbs.style.display = "none"; }
        if (rightNav) { rightNav.remove(); }
        if (additionalInfo) { additionalInfo.style.display = "none"; }
        if (landingBanner) { 
            landingBanner.classList.remove("landing-banner"); 
            var landingImage = document.querySelector("figure");
            landingImage.style.marginTop = "0";
        }

        //Remove bootstrap classes that provide adaptive styling
        if (containerDiv) { 
            containerDiv.classList.remove("container"); 
            containerDiv.style.paddingTop = '0';
            containerDiv.style.marginRight = "2rem";
        }
        if (contentDiv) { contentDiv.classList.remove("col-xl-8"); }

        //Process optional query string vars to hide title, intro, prev next buttons
        if (hideTitle == 'true') {
            var myTitle = document.querySelector('h1');
            myTitle.style.display = "none";
        }
        if (hideIntro == 'true') {
            var firstLeadParagraph = document.querySelector('p.lead');
            firstLeadParagraph.style.display = "none";
        }
        if (showPrevNext != 'true') {
            var btnNavContainer = document.querySelector('.btn-nav-container');
            if (btnNavContainer) { btnNavContainer.style.display = "none"; }
        }
    }

    // handeEmbedLinks either adds the query string or target="_top" depending on context
    function handleEmbedLinks() {
        const links = document.querySelectorAll('a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href !== null) {
                if (showPrevNext == 'true') {
                    if (href.startsWith('http://') || href.startsWith('https://')) {
                        addTargetTopToLink(link);
                    } else {
                        link.setAttribute('href', href + queryString);
                    }
                } else {
                    addTargetTopToLink(link);
                }
            }
        });
    }

    function addTargetTopToLink(link) {
        link.setAttribute('target', '_top');
    }
})();
// END Script to turn on embed mode

// START Script to show embed modal

// Updates code, copies code etc.
 
// Unlikely hide-title and hide-intro would be used in concert with show-prev-next 
const copyCodeButton = document.getElementById("copy-code");
const feedback = document.getElementById("feedback");
const embedCodeBox = document.getElementById('embedCode')

function updateEmbedCode() {
	const hideTitle = document.getElementById('hideTitle').checked;
	const hideIntro = document.getElementById('hideIntro').checked;
	const currentUrl = window.location.origin + window.location.pathname;
	let url = `${currentUrl}?iframe=true`;
	if (hideTitle) url += '&hide-title=true';
	if (hideIntro) url += '&hide-intro=true';

	const embedCode = `<iframe src="${url}" width="100%" height="100%" scrolling="no" style="overflow:hidden"></iframe>`;
	embedCodeBox.value = embedCode;

}


//Called when "Copy code" is clicked. Copy the code to clipboard  (won't work on http:// only https:// )
function copyCode(e) {
	console.log("Code copied");
    navigator.clipboard.writeText(embedCodeBox.value);
	
	feedback.innerHTML = "Code copied to clipboard";
	feedback.classList.add("show");
}

// Initialize the embed code on page load
document.addEventListener('DOMContentLoaded', function() {
	if (copyCodeButton) {
		updateEmbedCode();
		copyCodeButton.addEventListener("click", copyCode);
	}
});
//END Script to handle embed modal


// START Dark mode
//There is additional code located in the <head> section of each page. It's not linked to an exterrnal js to minimise flash between content.
(function() {
  'use strict';

  const getStoredTheme = () => localStorage.getItem('theme');

  const setStoredTheme = theme => localStorage.setItem('theme', theme);

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    return storedTheme ? storedTheme : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  };

  const setTheme = theme => {
    const themeToSet = theme === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : theme;
    document.documentElement.setAttribute('data-bs-theme', themeToSet);
  };

  const showActiveTheme = theme => {
    document.querySelectorAll('.theme-switch').forEach(themeSwitcher => {
      themeSwitcher.querySelectorAll('[data-bs-theme-value]').forEach(element => {
        element.checked = (element.getAttribute('data-bs-theme-value') === theme);
      });
    });
  };

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const storedTheme = getStoredTheme();
    if (storedTheme !== 'light' && storedTheme !== 'dark') {
      setTheme(getPreferredTheme());
    }
  });

  window.addEventListener('DOMContentLoaded', () => {
    showActiveTheme(getPreferredTheme());
    document.querySelectorAll('.theme-switch [data-bs-theme-value]').forEach(toggle => {
      toggle.addEventListener('change', () => {
        const theme = toggle.getAttribute('data-bs-theme-value');
        setStoredTheme(theme);
        setTheme(theme);
        showActiveTheme(theme);
      });
    });
  });
})();
// END Dark mode
    