<?php
/**
 * What tools/setup-content.php puts into WordPress. Each page/post body is a block-markup file next to this one.
 * "desc" becomes the page excerpt and the Yoast meta description (keep it under ~155 characters).
 *
 * @package reorder-desk
 */

return array(
	'pages' => array(
		'home'               => array(
			'title' => 'Reorder Desk: stock forecasts and purchase orders for Shopify',
			'file'  => 'pages/home.html',
			'seo'   => 'Reorder Desk: stock forecasts and purchase orders for Shopify',
			'desc'  => 'Know what to reorder before it runs out. Sales forecasts with the reason behind every number, purchase orders per supplier, and receiving into Shopify.',
		),
		'features'           => array(
			'title'    => 'Features',
			'file'     => 'pages/features.html',
			'template' => 'page-landing',
			'seo'      => 'Features: reorder list, purchase orders, receiving',
			'desc'     => 'Everything in Reorder Desk: explainable forecasts, purchase orders by email, receiving with undo, bundles, supplier import, alerts and dead stock.',
		),
		'how-it-works'       => array(
			'title'    => 'How forecasts work',
			'file'     => 'pages/how-it-works.html',
			'template' => 'page-landing',
			'seo'      => 'How Reorder Desk works out what to reorder',
			'desc'     => 'The exact sums behind every suggestion: weighted sales per day, stock-out days left out, lead time, safety stock, case packs and minimum orders.',
		),
		'pricing'            => array(
			'title'    => 'Pricing',
			'file'     => 'pages/pricing.html',
			'template' => 'page-landing',
			'seo'      => 'Pricing: free for 50 variants, Pro $29/month',
			'desc'     => 'Reorder Desk is free for your 50 best-selling variants. Pro adds unlimited variants, several locations and seasonal forecasts. Billed through Shopify.',
		),
		'stocky-alternative' => array(
			'title'    => 'A Stocky alternative for Shopify',
			'file'     => 'pages/stocky-alternative.html',
			'template' => 'page-landing',
			'seo'      => 'Stocky alternative: forecasts and purchase orders for Shopify',
			'desc'     => 'Stocky closed on 31 August 2026. Reorder Desk brings back reorder suggestions and purchase orders, and imports your suppliers from a sheet.',
		),
		'help'               => array(
			'title' => 'Help centre',
			'file'  => 'pages/help.html',
			'seo'   => 'Help centre: getting started with Reorder Desk',
			'desc'  => 'How to set up Reorder Desk: first sync, suppliers, the reorder list, purchase orders, receiving stock, bundles, emails, locations and plans.',
		),
		'early-access'       => array(
			'title'    => 'Early access',
			'file'     => 'pages/early-access.html',
			'template' => 'page-landing',
			'seo'      => 'Get early access to Reorder Desk',
			'desc'     => 'Join the Reorder Desk early-access list and get one email on the day the app goes live on the Shopify App Store.',
		),
		'contact'            => array(
			'title'    => 'Contact',
			'file'     => 'pages/contact.html',
			'template' => 'page-landing',
			'seo'      => 'Contact Reorder Desk',
			'desc'     => 'Questions about Reorder Desk, a purchase order or your plan? Send us a message; we reply within one working day.',
		),
		'privacy-policy'     => array(
			'title' => 'Privacy policy',
			'file'  => 'pages/privacy-policy.html',
			'seo'   => 'Privacy policy',
			'desc'  => 'What this website and the Reorder Desk app collect, why, how long it is kept and how to have it deleted.',
		),
		'terms'              => array(
			'title' => 'Terms of use',
			'file'  => 'pages/terms.html',
			'seo'   => 'Terms of use',
			'desc'  => 'The terms for using the Reorder Desk website and the Reorder Desk Shopify app.',
		),
		'blog'               => array(
			'title' => 'Blog',
			'file'  => '',
			'seo'   => 'Blog: inventory planning for Shopify stores',
			'desc'  => 'Practical guides for Shopify stores that buy from suppliers: reorder points, purchase orders, dead stock and life after Stocky.',
		),
	),
	'posts' => array(
		'stocky-shut-down-what-now'    => array(
			'title'    => 'Stocky has shut down: how to keep reordering without it',
			'file'     => 'posts/stocky-shut-down-what-now.html',
			'category' => 'Stocky',
			'date'     => '2026-10-15 09:00:00',
			'cover'    => 'cover-stocky.png',
			'desc'     => 'Stocky stopped working on 31 August 2026. What it did, what Shopify still gives you, and a step-by-step plan to rebuild suppliers and purchase orders.',
		),
		'how-to-calculate-reorder-point' => array(
			'title'    => 'How to calculate a reorder point (with a worked example)',
			'file'     => 'posts/how-to-calculate-reorder-point.html',
			'category' => 'Guides',
			'date'     => '2026-10-14 09:00:00',
			'cover'    => 'cover-reorder-point.png',
			'desc'     => 'The reorder point formula in plain words: sales per day, lead time and safety stock, worked through for a real product, plus the mistakes to avoid.',
		),
		'case-packs-and-minimum-orders' => array(
			'title'    => 'Case packs and minimum orders: getting purchase quantities right',
			'file'     => 'posts/case-packs-and-minimum-orders.html',
			'category' => 'Guides',
			'date'     => '2026-10-13 09:00:00',
			'cover'    => 'cover-case-packs.png',
			'desc'     => 'Why supplier case packs and minimum order quantities change what you should order, how to round correctly, and when to split or combine orders.',
		),
		'find-and-fix-dead-stock'      => array(
			'title'    => 'Dead stock: how to find it, and stop buying more of it',
			'file'     => 'posts/find-and-fix-dead-stock.html',
			'category' => 'Guides',
			'date'     => '2026-10-12 09:00:00',
			'cover'    => 'cover-dead-stock.png',
			'desc'     => 'How to spot products that stopped selling, work out the cash tied up in them, and clear them without wrecking your margins.',
		),
	),
);
