const React = require("react-native");
const { Dimensions, Platform } = React;
const deviceHeight = Dimensions.get("window").height;
const deviceWidth = Dimensions.get("window").width;

export default {
  pageincontent:{
    marginTop:110
  },
  pinchead:{
    flex:1,
    justifyContent: 'center',
        alignItems: 'center',
  },pincmenu:{
    flex:1,
    flexDirection:'row',
    marginTop:15,
    marginBottom:15
  },pincmitem:{
    flex:0.333,
    justifyContent: 'center',
        alignItems: 'center',
        borderStyle: 'solid',
        borderLeftWidth: 1,
        borderLeftColor:'#CDD0D2'
  },pincmiicon:{
    fontSize:50,
    color:'#CDD0D2'
  },pincmitext:{
    color:'#CDD0D2',
    fontSize:12
  }
};
