module.exports = {
  title: 'superV',
  description: 'A platform for Laravel',

  themeConfig: {
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Github', link: 'https://github.com/superv/platform' },
    ],
    displayAllHeaders: true,
    sidebar: [
      {
        title: 'Introduction',
        collapsable: false,
        children: [
          '/introduction/01-key-features',
          // '/introduction/02-provides',
        ]
      },
      {
        title: 'Getting Started',
        collapsable: true,
        children: [
          ['/getting-started/INSTALLATION', 'Installation'],
        ]
      },
      {
        title: 'Core Concepts',
        collapsable: false,
        children: [
          '/concepts/01-addons',
          '/concepts/02-migrations',
        ]
      },
    ]
  }
}