/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          500: '#80aa40',
          600: '#72a336',
          700: '#5f862b',
          800: '#4c6a22',
          900: '#3d5419',
        }
      }
    },
  },
  plugins: [],
}