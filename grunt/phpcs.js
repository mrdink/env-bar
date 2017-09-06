module.exports = {
	/**
	 * grunt-sass
	 *
	 * Compile Sass to CSS using node-sass
	 *
	 * @link https://www.npmjs.com/package/grunt-sass
	 */
	php: {
		src: ['*.php']
	},
	options: {
		bin: "vendor/bin/phpcbf --extensions=php --ignore=\"*/vendor/*,*/grunt/*,*/node_modules/*,*/bower_components/*\"",
		standard: "phpcs.ruleset.xml"
	}
};
