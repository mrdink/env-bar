module.exports = {
	'default': [
		'makepot',
		'phpcs'
	],
    'build': [
        'default',
		'clean:dist',
        'copy:build'
    ],
	'tag': [
		'build',
		'copy:tag',
		'copy:trunk'
	]
};
