/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './app/Livewire/**/*.php',
  ],
  theme: {
    extend: {
      screens: {
        '2xs': '448px',
        'xs': '544px',
      },
      borderRadius: {
        xl: '1rem',
        '2xl': '1.5rem',
      },
      fontSize: {
        'kid-xl': ['1.4rem', '1.8rem'],
        'kid-2xl': ['1.8rem', '2.2rem'],
      },
    },
  },
  plugins: [],
}

