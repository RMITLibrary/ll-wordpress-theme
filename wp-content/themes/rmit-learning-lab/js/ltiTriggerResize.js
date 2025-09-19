// ltiTriggerResize.js
// This script is used to dynamically resize iframes in various LMS platforms using the standardized 'lti.frameResize' postMessage protocol.

/**
 * Resizes the iframe to match the content height.
 * This uses the 'lti.frameResize' subject, which may be supported by multiple LMS platforms.
 */
function resizeIframe() {
    // Calculate the new height of the iframe based on the content's scroll height.
    var newHeight = document.body.scrollHeight;
    
    // Send a postMessage to the parent window requesting the iframe size change.
    // This uses the 'lti.frameResize' message format, potentially standardized across some LMS platforms.
    window.parent.postMessage({
        subject: 'lti.frameResize',
        height: newHeight
    }, '*');
}

// Add event listeners to trigger a resize when the page loads or resizes occur.
window.addEventListener('load', resizeIframe);   // Trigger on page load
window.addEventListener('resize', resizeIframe); // Trigger on window resize

// Set an interval to resize periodically to handle dynamic content changes.
setInterval(resizeIframe, 1000);
