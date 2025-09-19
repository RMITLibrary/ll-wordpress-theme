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
					<div class="ask-the-library">
						<a href="https://www.rmit.edu.au/library/about-and-contacts/ask-the-library">
							<img src="https://rmitlibrary.github.io/cdn/image/svg/ask-the-library.svg" class="ask-logo" />
							<div class="ask-text">
								<p class="lead">Still can't find what you need?</p>
								<p>The RMIT University Library provides study support, one-on-one consultations and peer mentoring to RMIT students.</p>
							</div>
						</a>
					</div>
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
                    <div class="acknowledgement-container">
                        <div class="acknowledgement-image">
                            <img src="https://www.rmit.edu.au/content/dam/rmit/images/luwaytini.png" alt="" />
                        </div>

                        <div class="content">
                            <img alt="aboriginal flag" src="https://www.rmit.edu.au/content/dam/rmit/au/en/news/homepage/flag-red.png" />
                            <img alt="torres strait flag" src="https://www.rmit.edu.au/content/dam/rmit/au/en/news/homepage/flag-green.png">
                            
                            <h2 class="h4 margin-top-zero">Acknowledgement of Country</h2>
                            <p>RMIT University acknowledges the people of the Woi wurrung and Boon wurrung language groups of the eastern Kulin Nation on whose unceded lands we conduct the business of the University. RMIT University respectfully acknowledges their Ancestors and Elders, past and present. RMIT also acknowledges the Traditional Custodians and their Ancestors of the lands and waters across Australia where we conduct our business - Artwork 'Luwaytini' by Mark Cleaver, Palawa.</p>
                            <a href="https://www.rmit.edu.au/about/our-values/respect-for-australian-indigenous-cultures" class="link-large">More information</a>
                        </div>
                    </div>
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
					<li><a href="">About Learning Lab</a></li>
					<li><a href="">For educators</a></li>
					<li><a href="">Contribute</a></li>
					<li><a href="">Contact us</a></li>
				</ul>
                <!-- END links -->
                <!-- START legal-social -->
                <div class="footerlegalShareItems">
                    <!-- START legal -->
                    <div class="footer-legal">
                    <ul>
                        <li>Copyright © 2024 RMIT University<span> | </span></li>
                        <li><a href="https://www.rmit.edu.au/utilities/terms"><span class="no-focus" tabindex="-1">Terms</span></a><span> | </span></li>
                        <li><a href="https://www.rmit.edu.au/utilities/privacy"><span class="no-focus" tabindex="-1">Privacy</span></a><span> | </span></li>
                        <li><a href="https://www.rmit.edu.au/utilities/accessibility"><span class="no-focus" tabindex="-1">Accessibility</span></a> <span> | </span></li>
                        <li><a data-analytics-type="footerlink" data-analytics-value="Website feedback" href="https://www.rmit.edu.au/utilities/website-feedback"><span class="no-focus" tabindex="-1">Website feedback</span></a><span> | </span></li>
                        <li><a data-analytics-type="footerlink" data-analytics-value="Complaints" href="https://www.rmit.edu.au/utilities/complaints"><span class="no-focus" tabindex="-1">Complaints</span></a><span> | </span></li>
                        <li>ABN 49 781 030 034<span> | </span></li>
                        <li>CRICOS provider number: 00122A<span> | </span></li>
                        <li>TEQSA provider number: PRV12145<span> | </span></li>
                        <li>RTO Code: 3046<span> | </span></li>
                        <li><a href="https://www.open.edu.au/online-courses/rmit-university"><span class="no-focus" tabindex="-1">Open Universities Australia</span></a> </li>
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
                        <li><a href="https://twitter.com/rmit"> <span class="no-focus" tabindex="-1"><img src="https://rmitlibrary.github.io/cdn/image/svg/social/twitter.svg" alt="" /></span></a></li>
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

	</body>
</html>

