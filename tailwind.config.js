module.exports = {
	content: [
		"./**/*.{php,html,blade.php,js,ts,jsx,tsx}"
	],
	theme: {
		extend: {
			screens: {
				mobile: '480px',
				tablet: '768px',
				desktop: '1024px',
			},
			fontFamily: {
				sans: ['ui-sans-serif', 'system-ui', 'sans-serif', '"Apple Color Emoji"', '"Segoe UI Emoji"', '"Segoe UI Symbol"', '"Noto Color Emoji"'],
				serif: ['ui-serif', 'Georgia', 'Cambria', '"Times New Roman"', 'Times', 'serif'],
				mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', '"Liberation Mono"', '"Courier New"', 'monospace'],
				poppins: ['Poppins', 'sans-serif'],
			}
		},
	},
	plugins: [],
}
