// ltiTriggerResize.js
// This script is used to dynamically resize iframes in various LMS platforms using the standardized 'lti.frameResize' postMessage protocol.

/**
 * Resizes the iframe to match the content height.
 * This uses the 'lti.frameResize' subject, which may be supported by multiple LMS platforms.
 */
(function() {
    if ( window.parent === window ) {
        return;
    }

    var lastReportedHeight = 0;

    function resizeIframe(forceResize) {
        // Calculate the new height of the iframe based on the content's scroll height.
        var newHeight = document.documentElement.scrollHeight;

        if ( ! forceResize && Math.abs( newHeight - lastReportedHeight ) < 4 ) {
            return;
        }

        lastReportedHeight = newHeight;

        // Send a postMessage to the parent window requesting the iframe size change.
        // This uses the 'lti.frameResize' message format, potentially standardized across some LMS platforms.
        window.parent.postMessage(
            {
                subject: 'lti.frameResize',
                height: newHeight
            },
            '*'
        );
    }

    // Add event listeners to trigger a resize when the page loads or resizes occur.
    window.addEventListener('load', function() {
        resizeIframe(true);
    });

    window.addEventListener('resize', function() {
        resizeIframe(false);
    });

    // Set an interval to resize periodically to handle dynamic content changes.
    setInterval(function() {
        resizeIframe(false);
    }, 1000);
})();
