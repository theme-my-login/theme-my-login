module.exports = {
	extends: [ '@commitlint/config-conventional' ],
	rules: {
		'type-enum': [
			2,
			'always',
			[ 'feat', 'fix', 'chore', 'ci', 'docs', 'build', 'perf', 'refactor', 'test' ],
		],
	},
};
