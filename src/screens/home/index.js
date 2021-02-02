import React, { Component } from "react";
import { ImageBackground, View, StatusBar,Image,AsyncStorage, Alert,ScrollView } from "react-native";
import {  Container, H3, Text, Header, Title, Content, Button, Icon, Body, Left, Right, Item, Input, Form , ListItem, CheckBox, Spinner} from "native-base";
import SplashScreen from 'react-native-splash-screen'

import styles from "./styles";
import OneSignal from 'react-native-onesignal';
const logo = require("../../../assets/logo.png");
const launchscreenBg = require("../../../assets/launchscreen-bg.png");
const launchscreenLogo = require("../../../assets/logo-kitchen-sink.png");
const FBSDK = require('react-native-fbsdk');
import {NavigationActions, StackActions} from 'react-navigation';

const {
  LoginButton,
  AccessToken
} = FBSDK;
const {
  LoginManager,
} = FBSDK;

class Home extends Component {
  static navigationOptions = {
    drawerLockMode: 'locked-closed'
  }
    constructor(props) {
        super(props)
        this.state = {
          hatirla:false, token:null, userid:null,
          telefon:null, sifre:null, loader:true, oneId:null
        };
        getirAnasayfa = this.getirAnasayfa.bind(this);
    }
    getirAnasayfa(){
      this.props.navigation.navigate("Drawer");
    }
    componentWillMount() {
		    OneSignal.init("12e688ca-4cc1-435f-9d59-e743f3e98c11");
       OneSignal.addEventListener('received', this.onReceived);
       OneSignal.addEventListener('ids', this.onIds.bind(this));
       OneSignal.inFocusDisplaying(0);
       this.getValue();
     }
     componentWillUnmount(){
       OneSignal.removeEventListener('ids', this.onIds);
       OneSignal.removeEventListener('received', this.onReceived);
     }
     onIds(device) {
       console.log(device);
       this.setState({
         oneId:device.userId
       })
     }
     onReceived(notification) {
        console.log("Notification received: ", notification);
    }
     async setTokenandId() {
        await AsyncStorage.setItem('token', this.state.token);
        await AsyncStorage.setItem('userid', this.state.userid);
        global.token = this.state.token;
        global.userid = this.state.userid;

		 let resetToHome = StackActions.reset({
                        index: 0,
                        actions: [NavigationActions.navigate({routeName: 'Drawer'})],
                        key: null,
                    });
                    this.props.navigation.dispatch(resetToHome);

      }
      async getValue() {
        try {
             const value = await AsyncStorage.getItem('token');
             const uivalue = await AsyncStorage.getItem('userid');
               if (value !== null) {
                 global.token = value;
                 global.userid = uivalue;
                  //this.props.navigation.navigate("Anasayfa");
				  let resetToHome = StackActions.reset({
                        index: 0,
                        actions: [NavigationActions.navigate({routeName: 'Drawer'})],
                        key: null,
                    });
                    this.props.navigation.dispatch(resetToHome);
               }else{
                 SplashScreen.hide();
               }
               this.setState({loader:false});
            } catch (error) {
              Alert.alert(error);
          }
       }
     oturumuac(){
       var phone = this.state.telefon;
       var password = this.state.sifre;
       if(!phone || !password){
         Alert.alert("Bilgilendirme","Lütfen telefon numaranızı ve şifrenizi giriniz",[
           {text: 'Kapat', onPress: () => null}
         ]);
       }else{
         this.setState({ loader: true});
         fetch(global.apiurl,
               {
                   method: 'POST',
                   headers:
                   {
                       'Accept': 'application/json',
                       'Content-Type': 'application/json',
                   },
                   body: JSON.stringify(
                   {
                     p : 'nologin',
                     s : 1,
                     phone:this.state.telefon,
                     password:this.state.sifre,
                     oneId:this.state.oneId
                   })

               }).then((response) => response.json()).then((jr) =>
               {
                 if(jr["status"] == "1"){
                   this.setState({ token: jr.message.login_token, userid: jr.message.id});
                   this.setTokenandId();
                 }else if(jr["status"] == "2"){
                    global.smsuserid = jr.message.id;
                     this.props.navigation.navigate("smsonay");
                 }else{
                   Alert.alert("Bilgilendirme",jr.message,[
                     {text: 'Kapat', onPress: () => null}
                   ]);
                 }
                this.setState({ loader : false });
               }).catch((error) =>
               {
                   Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                     {text: 'Kapat', onPress: () => null}
                   ]);
                   this.setState({ loader : false});
               });
       }
     }

     facebookConnect(){
       let _this = this;
       LoginManager.logInWithReadPermissions(['public_profile',"email"]).then(
      function(result) {
        if (result.isCancelled) {
          Alert.alert("Bilgilendirme","İşlem iptal edildi",[
            {text: 'Kapat', onPress: () => null}
          ]);
        } else {
          AccessToken.getCurrentAccessToken().then(
            (data) => {
              let accessToken = data.accessToken;
              fetch('https://graph.facebook.com/me?fields=email,name&access_token=' + accessToken)
              .then((response) => response.json())
              .then((json) => {
                fetch(global.apiurl,
                      {
                          method: 'POST',
                          headers:
                          {
                              'Accept': 'application/json',
                              'Content-Type': 'application/json',
                          },
                          body: JSON.stringify(
                          {
                            p : 'nologin',
                            s : 8,
                            id:json.id,
                            name:json.name,
                            email:json.email
                          })

                      }).then((response) => response.json()).then( async (jr) =>
                      {
                        if(jr.status == 1){
                         await AsyncStorage.setItem('token', jr.message.login_token);
                          await AsyncStorage.setItem('userid', jr.message.id);
                          global.token = jr.message.login_token;
                          global.userid = jr.message.id;
                          this.getirAnasayfa();
                        }else{
                          Alert.alert("Bilgilendirme",jr.message,[
                            {text: 'Kapat', onPress: () => null}
                          ]);
                        }
                      }).catch((error) =>
                      {
                          Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                            {text: 'Kapat', onPress: () => null}
                          ]);
                      });

              })
              .catch(() => {
                Alert.alert("Bilgilendirme","Oturum açma hatası oluştu",[
                  {text: 'Kapat', onPress: () => null}
                ]);
              });

                          }
                        )
        }
      },
      function(error) {
        Alert.alert('Hata oluştu: ' + error);
  }
);
     }
  render() {
    return (
      <Container>
      {
        this.state.loader?
<View style={styles.indicator}>
<Spinner color='green' />
</View>:null
}
        <StatusBar barStyle="dark-content" backgroundColor="#ffffff" />
          <ScrollView style={{backgroundColor:"#fff"}}>
        <ImageBackground style={styles.imageContainer}>
          <View style={styles.GenelContainer}>
            <Image source={logo} style={styles.logo} resizeMode="contain" ></Image>
            <Form style={styles.formlogin}>
              <Item  style={styles.inputitem}>
                <Icon style={styles.loginicons} type="Ionicons" active name="call" />
                <Input placeholder="535*******" maxLength={10} keyboardType="phone-pad" onChangeText={ (text) => this.setState({ telefon: text }) } />
              </Item>
              <Item style={styles.inputitem}>
                <Icon style={styles.loginiconssifre} type="Ionicons" active name="lock" />
                <Input Text="password" secureTextEntry={true} placeholder="**********" onChangeText={ (text) => this.setState({ sifre: text }) } />
              </Item>
            </Form>
            <View style={{flexDirection: 'row'}}>
              <View style={styles.twolayoutiki}>
                <ListItem button style={{height:14,marginLeft:0,paddingTop:10,borderBottomWidth:0}}>
                  <CheckBox style={{backgroundColor: this.state.hatirla?'#00a0b1':'#E5E8EB',borderColor: '#E6E7EB'}}  checked={this.state.hatirla} onPress={() => this.setState({ hatirla: !this.state.hatirla })} />
                  <Body>
                    <Text>Hatırla</Text>
                  </Body>
                </ListItem>
              </View>
              <View style={styles.twolayout}>
                <H3 style={styles.sifremiunuttum} onPress={()=>this.props.navigation.navigate("sifreunuttum")}>Şifremi Unuttum ?</H3>
              </View>
            </View>
            <Button style={styles.girisbtn} onPress={() => this.oturumuac()}>
              <Text style={styles.txt} uppercase={false}>Giriş Yap</Text>
            </Button>
            <Button transparent style={styles.kayitbtn} onPress={() => this.props.navigation.navigate("kayit")}>
              <Text style={styles.txtkayit} uppercase={false}>Kayıt Ol !</Text>
            </Button>

          </View>
        </ImageBackground>
          </ScrollView>
      </Container>
    );
  }
}

export default Home;
