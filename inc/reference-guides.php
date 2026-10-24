<?php
/**
 * Reference guides: theme-built pages that answer one claim question with
 * rules and citations (the appraisal clause, diminished value by state, and
 * so on). Each has its own template; this registry gives them their SEO
 * title and description, the same way mat_get_tools_registry() does for
 * tools.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mat_get_reference_guides() {
	return array(
		'insurance-appraisal-clause' => array(
			'title'     => __( 'Car Insurance Appraisal Clause: How to Invoke It', 'myautotriage' ),
			'seo_title' => __( 'Car Insurance Appraisal Clause: How It Works and Costs', 'myautotriage' ),
			'meta'      => __( 'Can\'t agree with your own insurer on the repair cost or total loss value? How the appraisal clause works, the steps, who pays the appraisers and umpire, and state rules.', 'myautotriage' ),
			'template'  => 'page-templates/template-appraisal-guide.php',
		),
		'diminished-value-by-state' => array(
			'title'     => __( 'Diminished Value Claim Rules by State', 'myautotriage' ),
			'seo_title' => __( 'Diminished Value Claims by State: Own Insurer vs At-Fault', 'myautotriage' ),
			'meta'      => __( 'Can you claim diminished value from your own insurer or only the at-fault driver\'s? The rule in all 50 states and DC, with the court case or law behind each.', 'myautotriage' ),
			'template'  => 'page-templates/template-dv-by-state.php',
		),
	);
}

function mat_current_reference_guide( $post ) {
	$guides = mat_get_reference_guides();
	if ( ! $post || 'page' !== $post->post_type || $post->post_parent || ! isset( $guides[ $post->post_name ] ) ) {
		return null;
	}
	return $guides[ $post->post_name ];
}

/**
 * A copy-ready text block (assets/js/main.js wires the data-mat-copy button).
 */
function mat_copy_block( $id, $text ) {
	echo '<div class="mat-adj-reply"><p id="' . esc_attr( $id ) . '" class="mat-adj-reply__text" style="white-space:pre-line;">' . esc_html( $text ) . '</p>';
	echo '<button type="button" class="mat-btn mat-btn--ghost mat-btn--sm" data-mat-copy="' . esc_attr( $id ) . '">' . esc_html__( 'Copy text', 'myautotriage' ) . '</button></div>';
}
