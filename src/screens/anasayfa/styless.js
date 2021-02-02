const React = require("react-native");
const { Dimensions, Platform,StyleSheet } = React;
let { width, height } = Dimensions.get('window');

export default {
  map: {
      width: width,
      height: height,
      zIndex:1,
      opacity: 1,

  },
  inputview:  {
    backgroundColor: 'rgba(0,0,0,0)',
            position: 'absolute',
            top: 120,
            left: 15,
            right: 15,
            zIndex:9999
  },
  inputview2:  {
    backgroundColor: 'rgba(0,0,0,0)',
            position: 'absolute',
            bottom: 20,
            left: 15,
            right: 15,
            zIndex:9999
  },    inputview2adim2:  {
        backgroundColor: 'rgba(0,0,0,0)',
                position: 'absolute',
                top:170,
                left: 15,
                right: 15,
                zIndex:9999
      },
      inputview3adim3:  {
            backgroundColor: 'rgba(0,0,0,0)',
                    position: 'absolute',
                    top:230,
                    left: 15,
                    right: 15,
                    zIndex:9999
          },
          inputview4adim4:  {
                backgroundColor: 'rgba(0,0,0,0)',
                        position: 'absolute',
                        top:290,
                        left: 15,
                        right: 15,
                        zIndex:9999
              },
  nereyeara:{
    backgroundColor:'#fff',
    borderRadius:10,
    shadowColor: '#000',
 shadowOffset: { width: 10, height: 20 },
 shadowOpacity: 0.8,
 shadowRadius: 222,
  },
  nereyearaadim2:{
    backgroundColor:'#fff',
    borderRadius:10,
    shadowColor: '#000',
 shadowOffset: { width: 10, height: 20 },
 shadowOpacity: 0.8,
      marginTop:10,
 shadowRadius: 222,
  },
  nereyearaadim3:{
    backgroundColor:'#fff',
    borderRadius:10,
    shadowColor: '#000',
 shadowOffset: { width: 10, height: 20 },
 shadowOpacity: 0.8,
      marginTop:10,
 shadowRadius: 222,
  },
  nereyearaadim4:{
    backgroundColor:'#fff',
    borderRadius:10,
    shadowColor: '#000',
 shadowOffset: { width: 10, height: 20 },
 shadowOpacity: 0.8,
      marginTop:10,
 shadowRadius: 222,
  },
  adresnereyeicon:{
    color:'#6cbd45',
    marginLeft: 15,
    fontSize:20,
	width:30
  },

  adresara:{
    backgroundColor:'#fff',
    borderRadius:10,
    shadowColor: '#000',
 shadowOffset: { width: 10, height: 20 },
 shadowOpacity: 0.8,
 shadowRadius: 222,
  },
  adresarasecim:{
    backgroundColor:'#fff',
borderTopLeftRadius:10,
borderTopRightRadius:10,
    shadowColor: '#000',
 shadowOffset: { width: 10, height: 20 },
 shadowOpacity: 0.8,
 shadowRadius: 222,
  },
  adresaraicon:{
    color:'#9444a5',
    marginLeft: 15,
    fontSize:25,
	width:30
  },
  adresarainput:{
  }
};
