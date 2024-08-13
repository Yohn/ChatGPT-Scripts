<?php

/*
$tabsConfig = [
	'id' => 'exampleTabs',
	'nav_class' => 'nav-pills', // Example of custom nav class
	'fade' => true, // Enable fade effect
	'items' => [
		[
			'id' => 'tab1',
			'text' => 'Tab 1',
			'content' => 'Content for Tab 1',
		],
		[
			'id' => 'tab2',
			'text' => 'Tab 2',
			'content' => 'Content for Tab 2',
		],
		[
			'id' => 'tab3',
			'text' => 'Tab 3',
			'content' => 'Content for Tab 3',
		],
	],
];

$tabsRenderer = new TabsRenderer($tabsConfig);
echo $tabsRenderer->renderTabs();

TabsRenderer Class:
        Properties:
            $tabs: Holds the configuration array for the tabs component.
        Constructor:
            Accepts an array for the tabs configuration.
        Methods:
            renderTabs(): Constructs the HTML for the tabs, supporting features like fade effects, custom nav classes, and dynamic content.
