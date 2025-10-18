module.exports = {
  plugins: [
    'preset-default',
    {
      name: 'removeViewBox',
      active: false
    },
    {
      name: 'removeUselessStrokeAndFill', 
      active: false
    },
    {
      name: 'cleanupIds',
      active: false
    },
    {
      name: 'removeUselessDefs',
      active: false
    },
    {
      name: 'removeUnknownsAndDefaults',
      active: false
    },
    {
      name: 'convertStyleToAttrs',
      active: false
    },
    {
      name: 'convertColors',
      active: false
    }
  ]
};