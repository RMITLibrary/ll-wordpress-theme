</main>

	<?php if (function_exists("lc_custom_footer")) lc_custom_footer(); else {
		?>
		<?php if (is_active_sidebar( 'footerfull' )): ?>
		<div class="wrapper bg-light mt-5 py-5" id="wrapper-footer-widgets">
			
			<div class="container mb-5">
				
				<div class="row">
					<?php dynamic_sidebar( 'footerfull' ); ?>
				</div>

			</div>
		</div>
		<?php endif ?>
		
		
<div class="wrapper footer-container" id="wrapper-footer-colophon">
	<div class="container">
        <div class="row">
            <div class="col">
				<!-- START ask the library -->
				<div class="ask-container">
					
                    <section class="ask-the-library">
						<a href="https://www.rmit.edu.au/library/about-and-contacts/ask-the-library">
							<img src="https://rmitlibrary.github.io/cdn/footer/ask-library-icon-round.svg" class="ask-logo" alt="" />
							<div class="ask-text">
								<h2 class="h3 margin-top-zero">Ask the Library</h2>
								<p>Get help with finding information, referencing, and using the Library.</p>
							</div>
						</a>
					</section>
			
				</div>
				<!-- END ask the library -->
			</div>
 		</div>
	</div>
    <!-- START acknowledgement -->
    <div class="acknowledgement">
        <div class="container">
            <div class="row">
                <div class="col">
                <section class="acknowledgement-container">
                        <div class="content">
                            <img alt="aboriginal flag" src="https://www.rmit.edu.au/content/dam/rmit/au/en/news/homepage/flag-red.png" />
                            <img alt="torres strait flag" src="https://www.rmit.edu.au/content/dam/rmit/au/en/news/homepage/flag-green.png">
                            
                            <h2 class="h4 margin-top-zero">Acknowledgement of Country</h2>
                            <p>RMIT University acknowledges the people of the Woi wurrung and Boon wurrung language groups of the eastern Kulin Nation on whose unceded lands we conduct the business of the University. RMIT University respectfully acknowledges their Ancestors and Elders, past and present. RMIT also acknowledges the Traditional Custodians and their Ancestors of the lands and waters across Australia where we conduct our business<span class="img-credit"> - Artwork 'Sentient' by Hollie Johnson, Gunaikurnai and Monero Ngarigo</span>.</p>
                            <a href="https://www.rmit.edu.au/about/our-values/respect-for-australian-indigenous-cultures" class="link-large">More information</a>
                        </div>
                        <div class="acknowledgement-image">
                            <img src="https://www.rmit.edu.au/content/dam/rmit/images/sentient-hollie-johnson.jpg" alt="Abstract artwork titled 'Sentient' by Hollie Johnson, features a complex pattern of intersecting blue, green, and yellow lines over a white background showing the journeys of life starting, finishing and intersecting. The colouring of the line work illustrates the evolution of nature through many First Nations stories: the sun, the giver of life (yellow), the creation of land (green), and the sea (blue). Red and black concentric diamond and semi-circle shapes symbolise men and women respectively, connecting them to Ancestors who gave birth to them. The artwork is also a representation of RMIT and the experience within our community, culture and pathways." />
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>    
    <!-- END acknowledgement -->
    <!-- START footer -->
    <footer>
    <div class="container">
        <div class="row">
            <div class="col">
                <!-- START logo -->    
                <a aria-label="Royal Melbourne Institute of Technology University Logo" href="https://www.rmit.edu.au/">
					<div aria-hidden="true" class="logo"><span class="no-focus" tabindex="-1"><img src="https://rmitlibrary.github.io/cdn/image/svg/rmit-logo.svg" style="width: 100px" alt="" /></span>
					</div>
				</a>
                <!-- END logo -->
                <!-- START links -->
				<ul class="footer-links">
					<li><a href="/about-the-learning-lab/">About Learning Lab</a></li>
					<li><a href="/about-the-learning-lab/accessibility/">Accessibility</a></li>
					<li><a href="/about-the-learning-lab/whats-new/">What's new</a></li>
					<li><a href="https://forms.office.com/r/YvquUHdtE5">Learning Lab feedback</a></li>
				</ul>
                <!-- END links -->
                <!-- START legal-social -->
                <div class="footerlegalShareItems">
                    <!-- START legal -->
                    <div class="footer-legal">
                    <ul>
                        <li>Copyright © <?php echo date("Y"); ?> RMIT University</li>
                        <li><a href="https://www.rmit.edu.au/utilities/terms"><span class="no-focus" tabindex="-1">Terms</span></a></li>
                        <li><a href="https://www.rmit.edu.au/utilities/privacy"><span class="no-focus" tabindex="-1">Privacy</span></a></li>
                        <li>ABN 49 781 030 034</li>
                        <li>CRICOS provider number: 00122A</li>
                        <li>TEQSA provider number: PRV12145</li>
                        <li>RTO Code: 3046</li>
                        <li><a href="https://www.open.edu.au/courses/degrees/rmit"><span class="no-focus" tabindex="-1">Open Universities Australia</span></a> </li>
                      </ul>
                    </div>
                    <!-- END legal -->
                    <!-- START social -->
                    <div class="social-nav">
                    <ul> 
                        <!-- START facebook -->
                        <li><a aria-label="For Facebook"  href="https://www.facebook.com/RMITuniversity/"> <span class="no-focus" tabindex="-1"><img src="https://rmitlibrary.github.io/cdn/image/svg/social/facebook.svg" alt="" /></span></a></li>
                        <!-- END facebook -->
                        <!-- START twitter/x -->
                        <li><a aria-label="For Twitter" href="https://twitter.com/rmit"> <span class="no-focus" tabindex="-1"><img src="https://rmitlibrary.github.io/cdn/image/svg/social/twitter.svg" alt="" /></span></a></li>
                        <!-- END twitter/x -->
                        <!-- START insta -->
                        <li><a aria-label="For Instagram" href="https://www.instagram.com/rmituniversity/"> <span class="no-focus" tabindex="-1"><img src="https://rmitlibrary.github.io/cdn/image/svg/social/instagram.svg" alt="" /></span> </a></li>
                        <!-- END insta -->              
                        <!-- START LinkedIn -->
                        <li><a aria-label="For LinkedIn" data-analytics-type="socialshare" data-analytics-value="LinkedIn" href="https://www.linkedin.com/school/rmit-university/"><span class="no-focus" tabindex="-1"><img src="https://rmitlibrary.github.io/cdn/image/svg/social/linkedin.svg" alt="" /></span></a></li>
                        <!-- END LinkedIn -->
                        <!-- START YouTube -->
                        <li><a aria-label="For Youtube" href="https://www.youtube.com/user/rmitmedia"> <span class="no-focus" tabindex="-1"><img src="https://rmitlibrary.github.io/cdn/image/svg/social/youtube.svg" alt="" /></span></a></li>
                        <!-- END YouTube -->     
                        <!-- START Weibo -->          
                        <li><a aria-label="For Weibo" href="https://www.weibo.com/rmituni"> <span class="no-focus" tabindex="-1"><img src="https://rmitlibrary.github.io/cdn/image/svg/social/weibo.svg" alt="" /></span></a></li>
                        <!-- END Weibo -->                
					</ul>
					</div>
                    <!-- END social -->
                </div>
                <!-- END legal-social -->
            </div>
        </div>
    </div>
	</footer>
    <!-- END footer -->		
</div><!-- wrapper end -->
		
	<?php 
	} //END ELSE CASE ?>

	<?php wp_footer(); ?>

<!-- Code to handle hamburger menu, embed mode, modal, dark mode -->
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/main-body.js?v=1.0.9"></script>

<!-- Code to handle resizing iframes on this page -->
<script type="text/javascript" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/js/iframeResizer.min.js' ); ?>"></script>
<script type="text/javascript" data-theme-js-base="<?php echo esc_attr( get_stylesheet_directory_uri() . '/js/' ); ?>">
	(function () {
		var script = document.currentScript;
		var basePath = script ? script.getAttribute('data-theme-js-base') : '';

		if (!basePath) {
			var hostScript = document.querySelector('script[src$="iframeResizer.min.js"], script[src*="iframeResizer.min.js?"]');
			if (hostScript && hostScript.src) {
				basePath = hostScript.src.replace(/iframeResizer\.min\.js(?:\?.*)?$/, '');
			}
		}

		if (!basePath) {
			basePath = '/wp-content/themes/rmit-learning-lab/js/';
		}

		var selector = 'iframe[data-rmit-resize="host"]';
		if (document.querySelector(selector)) {
			iFrameResize({ warningTimeout: 0 }, selector);
		}

		var params = new URLSearchParams(window.location.search);
		if (params.get('iframe') === 'true') {
			['iframeResizer.contentWindow.min.js', 'ltiTriggerResize.js'].forEach(function (file) {
				var el = document.createElement('script');
				el.src = basePath + file;
				document.body.appendChild(el);
			});
		}
	})();
</script>
</body>
</html>
