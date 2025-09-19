

<?php //set variables for easy application later on - field keys are difficult to remember what they apply to when you're writing code
//essential
$llkeywords = get_field( "field_6527440d6f9a2" );
$llcategory = get_field("field_65275ce3c7e36");
?>


<!-- keywords echo - https://www.advancedcustomfields.com/resources/taxonomy/ -->      


<hr class="margin-top-xl">

<div class="keyword-embed-contain">
    <div class="keywords">
	<?php 
$terms = get_field('field_6527440d6f9a2');
if( $terms ): ?>
        <h2 class="h5">Keywords</h2>
        <ul>
        <?php foreach( $terms as $term ): ?>
            <li><a href="<?php echo esc_url( get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a></li>  
        <?php endforeach; ?>
        </ul>
<?php endif; ?> 
    </div>
    <button class="btn-embed" type="button" data-bs-toggle="modal" data-bs-target="#embedModal">Embed this page</button>
</div>

<!-- keywords echo end -->


<!-- START embed modal -->
<div class="modal fade" id="embedModal" tabindex="-1" aria-labelledby="embedModalLabel" aria-hidden="true">
	<!-- START modal-dialog -->
	<div class="modal-dialog">
		<!-- START modal-content -->
		<div class="modal-content">
			<div class="modal-header">
				<h2 class="h3 margin-top-zero margin-bot-zero" id="embedModalLabel">Embed this page</h2>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<!-- START modal-body -->
			<div class="modal-body">
				<textarea id="embedCode" class="code-section mt-3" rows="4" readonly aria-label="Embed Code"></textarea>
				<form id="embedForm">
					<div class="form-check-inline">
						<input class="form-check-input" type="checkbox" id="hideTitle" onchange="updateEmbedCode()" aria-describedby="hideTitleDescription">
						<label class="form-check-label small" for="hideTitle">Hide page title</label>
					</div>
					<div class="form-check-inline">
						<input class="form-check-input" type="checkbox" id="hideIntro" onchange="updateEmbedCode()" aria-describedby="hideIntroDescription">
						<label class="form-check-label small" for="hideIntro">Hide introduction</label>
					</div>
				</form>
				<div class="btn-container">
					<button id="copy-code" type="button" class="btn btn-primary">Copy code</button>
					<div id="feedback" class="collapse small"></div>
				</div>
				<!-- START accordion item -->
				<div class="accordion-item transcript margin-top-xs">
					<p class="accordion-header" id="Transcript-head">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#Transcript-body" aria-expanded="false" aria-controls="Transcript-body">
						How to embed in Canvas LMS
					</button>
					</p>
					<div id="Transcript-body" class="accordion-collapse collapse" aria-labelledby="Transcript-head">
					<div class="accordion-body padding-top-zero">
						<ol class="small">
							<li>Copy the iframe code above.</li>
							<li>Go to the course in Canvas where you want to add the content.</li>
							<li>Navigate to the page or module where you want to embed the content.</li>
							<li>In the Rich Content Editor, click on the "HTML Editor" link.</li>
							<li>Paste the iframe code into the HTML area.</li>
							<li>Switch back to the Rich Content Editor to see the embedded content.</li>
							<li>Save the changes to your page or module.</li>
						</ol>
						<p class="small">Note: Ensure that your permissions allow embedding external content in your Canvas LMS instance.</p>
					</div>
					</div>
				</div>
				<!-- END accordion item -->
			</div>
			<!-- END modal-body -->
		</div>
		<!-- END modal-content -->
	</div>
	<!-- END modal-dialog -->
</div>
<!-- END embed modal -->



 














