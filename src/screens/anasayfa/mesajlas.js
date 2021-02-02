import React, { Component } from "react";
import { ImageBackground,Dimensions, View, StatusBar,Image,TouchableOpacity, Alert,StyleSheet,KeyboardAvoidingView,ScrollView,TextInput,TouchableHighlight,Keyboard} from "react-native";
import { Container, Header, Title, Content, Text, Button, Icon, Footer, FooterTab, Left, Right, Body, Badge,List, ListItem,Thumbnail,Spinner} from "native-base";
import moment from 'moment';
require('moment/locale/tr.js');
import styles from "./styles";
import AutogrowInput from 'react-native-autogrow-input';
const deviceHeight = Dimensions.get("window").height;
import { GiftedChat } from 'react-native-gifted-chat'

let chatInterval=null;
const logo = require("../../../assets/logo.png");
function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
class mesajlas extends Component {
  constructor(props) {
    super(props);
    var messages = [];
    this.state = {
      messages: [],
      inputBarText: '',
      loader:true,
      paddingmsg:0
    }
  }
  onSend(messages = []) {
console.log(messages);
    let _this = this;
    this.setState({ loader : true});
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
                p : 'message',
                s : 4,
                userid:global.userid,
                token:global.token,
                id:global.chatid,
                msg:messages[0].text,
              })

          }).then((response) => response.json()).then((jr) =>
          {
            console.log(jr);
            if(jr["status"] == 1){

              this.setState(previousState => ({
                messages: GiftedChat.append(previousState.messages, messages),
              }));

            }else{
              Alert.alert("Bilgilendirme",jr.message,[
                {text: 'Kapat', onPress: () => null}
              ]);
            }
              this.setState({ loader : false});
          }).catch((error) =>
          {
              console.log(error);
              this.setState({ loader : false});
          });


 }


  componentWillMount () {
   this.chatInterval = setInterval(() => {
      this.getChatData();
    }, 10000);
    this.getChatData();

  }
  getChatData(){
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
                p : 'message',
                s : 3,
                userid:global.userid,
                token:global.token,
                id:global.chatid
              })

          }).then((response) => response.json()).then((jr) =>
          {
            if(jr["status"] == 1){
              console.log(jr);
              this.setState({
                messages:jr.message
              });
            }else{
              Alert.alert("Bilgilendirme",jr.message,[
                {text: 'Kapat', onPress: () => null}
              ]);
            }
            this.setState({ loader : false});
          }).catch((error) =>
          {
              console.log(error);
              this.setState({ loader : false});
          });
  }
  componentWillUnmount() {
    try{
      clearInterval(this.chatInterval._id);
    }catch(e){
      console.log(e);
    }
  }


  _sendMessage() {

  }

  render() {
    return (
      <Container style={styles.anasayfaGenel, {backgroundColor:'#fff'}}>
      {
        this.state.loader?
        <View style={styles.indicator}>
        <Spinner color='green' />
        </View>:null
    }
    <View style={styles.headerview, {marginBottom:20}}>
    <Header transparent style={styles.header, {backgroundColor:'#fff'}} androidStatusBarColor='#ffffff'>
      <Left style={{ flex: 1 }}>
        <Button style={styles.headeringeribtn} transparent onPress={() => this.props.navigation.goBack()}>
          <Icon style={styles.headeringeriicon} name="arrow-back" />
        </Button>
      </Left>
      <Body style={styles.logoOrtala}>
        <Image source={logo} style={styles.logo} resizeMode="contain" ></Image>
      </Body>
      <Right style={{marginTop:-10}}>
     
      </Right>
    </Header>
    </View>
      <GiftedChat
       messages={this.state.messages}
       onSend={messages => this.onSend(messages)}
       user={{
         _id: global.userid,
       }}
       placeholder="Göndermek istediğiniz mesajınızı yazın"
       locale="tr"
     />
     </Container>
    );
  }
}


export default mesajlas;
