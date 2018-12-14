const path = require('path')

module.exports = {
  baseUrl: '/superv/',

  outputDir: '../assets/',

  indexPath: process.env.NODE_ENV === 'production'
    ? '../views/spa.blade.php'
    : 'index.html',
}