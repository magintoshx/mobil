const React = require("react-native");
const { Dimensions, Platform } = React;
const deviceHeight = Dimensions.get("window").height;
const deviceWidth = Dimensions.get("window").width;

export default {
  imageContainer: {
    flex: 1,
    width: null,
    height: null,
    backgroundColor: '#FFF'
  },
  GenelContainer: {
    flex: 1,
    marginTop: deviceHeight / 8,
    marginBottom: 0
  },
  logo: {
    height: deviceHeight * 0.2,
    //width: deviceWidth * 0.65,
    alignSelf: 'center',
    marginBottom:10
  },
  alarm: {
    height: 35,
    width:35,
    alignSelf: 'center',
    marginBottom:10
  },
  text: {
    color: "#D8D8D8",
    bottom: 6,
    marginTop: 5
  },
  formlogin:{
    marginLeft:30,
    width:deviceWidth - 60,
    marginTop:20,
    marginBottom:10
  },
  kayitform:{
    marginLeft:20,
    width:deviceWidth - 60,
    marginTop:20,
    marginBottom:10
  },
  loginicons:{
    color: "#B0B1B7",
    paddingLeft:10,
    paddingRight:10
  },
  loginiconssifre:{
    color: "#B0B1B7",
    paddingLeft:12,
    paddingRight:12
  },
  inputitem:{
    borderStyle: 'solid',
    borderLeftWidth: 1,
    borderRightWidth: 1,
    borderTopWidth: 1,
    borderColor: '#E3E4E6',
    borderRadius: 5,
    marginTop:5,
    marginBottom:5,
    marginLeft:0
  },
  twolayout:{
    width:(deviceWidth / 2)-30,
    marginLeft:0,
    backgroundColor: '#fff',
    height:30
  },
  twolayoutiki:{
    width:(deviceWidth / 2)-30,
    marginLeft:30,
    backgroundColor: '#fff',
    height:30

  },
  sifremiunuttum:{
    fontSize:16,
    alignSelf: 'flex-end',
    color:"#B0B1B7"
  },
  girisbtn:{
    backgroundColor: "#F39200",
    alignSelf: "center",
    width:deviceWidth-60,
    marginLeft:0,
    borderRadius: 5,
    marginTop:15,
    justifyContent: "center"
  },
  kayitbtn:{
      backgroundColor:"#000",
    alignSelf: "center",
    width:deviceWidth-60,
    marginLeft:0,
    borderRadius: 5,
    marginTop:15,
    justifyContent: "center",
},
  txt:{
    fontSize:18,
	color:"#fff"
  },
  txtkayit:{
    fontSize:18,
    color: "#fff",
      backgroundColor:"transparent",
  },
  yada:{
    width:deviceWidth-60,
    alignSelf:"center",
    color:"#B0B1B7",
    marginLeft:0,
    textAlign: 'center',
    fontSize:12,
    justifyContent:"center",
      marginTop:10
  },
  facebooktxt:{
    width:deviceWidth-60,
    alignSelf:"center",
    color:"#3A5897",
    marginLeft:0,
    textAlign: 'center',
    fontSize:18,
    marginTop:10,
    textDecorationLine: "underline",
    justifyContent:"center"
  },
  facebookikon:{
    color: "#3A5897",
    fontSize:18,

  },
  kayitbar:{
     backgroundColor: "#B0B1B7"
  },
  kayitContainer: {
    flex: 1,
    marginTop: 0,
    marginBottom: 0
  },
  kosuldiv:{
    width:deviceWidth-60,
    marginLeft:20,
    height:30

  },
  indicator: {
    flex:1,
    position:'absolute',
    width: deviceWidth,
    height: deviceHeight,
    backgroundColor:'#fff',
    zIndex:9999999,
    opacity:0.6,
    justifyContent: 'center',
   alignItems: 'center'
 },blockbtn:{
     alignSelf: "center",
     width:deviceWidth-60,
     marginLeft:0,
     borderRadius: 5,
     marginTop:15,
     justifyContent: "center"
   },
   headeringeribtn: {
     color:'#000'
   },
   headeringeriicon: {
     color:'#000',
     fontSize: 40
   },
  blackBtn:{
    backgroundColor: "#000"
  }
};
