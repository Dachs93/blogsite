<?php

/*
 * Your CV. Edit the values, add or remove entries, then commit and push.
 *
 * Text in 'summary' is Markdown. Text in 'highlights', 'description' and 'notes'
 * allows inline Markdown: **bold**, *italic*, `code` and [links](https://example.com).
 * Leave a list empty ([]) to hide that section.
 */

return [
    'name' => 'Your Name',
    'headline' => 'What you do, in a few words',
    'location' => 'City, Country',
    'email' => 'you@example.com',

    // Shown in the contact line, in this order. Label => URL.
    'links' => [
        'GitHub' => 'https://github.com/',
        'LinkedIn' => 'https://www.linkedin.com/',
    ],

    'summary' => <<<'MD'
        Two or three sentences: what you're good at, what you've done, and what
        you're looking for next.
        MD,

    // Most recent first.
    'experience' => [
        [
            'role' => 'Job title',
            'company' => 'Company',
            'dates' => '2022 – Present',
            'location' => 'City',
            'tech' => 'Main tools and technologies',
            'highlights' => [
                'Something you delivered, with a number if you have one.',
                'Another responsibility or achievement.',
            ],
        ],
        [
            'role' => 'Earlier job title',
            'company' => 'Earlier company',
            'dates' => '2018 – 2022',
            'location' => 'City',
            'tech' => '',
            'highlights' => [
                'What you did there.',
            ],
        ],
    ],

    'education' => [
        [
            'qualification' => 'Qualification',
            'institution' => 'Institution',
            'dates' => '2014 – 2018',
            'notes' => 'Grade, thesis or focus',
        ],
    ],

    // Group name => items.
    'skills' => [
        'Languages' => ['List', 'them', 'here'],
        'Frameworks and tools' => ['List', 'them', 'here'],
        'Other' => ['Spoken languages', 'certifications'],
    ],

    'projects' => [
        [
            'name' => 'Project name',
            'url' => 'https://example.com',
            'description' => 'One or two sentences on what it does and why it exists.',
            'tech' => 'Tools and technologies',
        ],
    ],
];
