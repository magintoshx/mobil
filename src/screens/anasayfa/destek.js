import React, { Component } from "react";
import { ImageBackground, View, StatusBar,Image,TouchableOpacity, Alert} from "react-native";
import { Container, Header, Title, Content, Text, Button, Icon, Footer, FooterTab, Left, Right, Body,Badge, Form,Item, Input,Textarea, Spinner} from "native-base";

import styles from "./styles";

const logo = require("../../../assets/logo.png");
class destek extends Component {
  constructor(props) {
      super(props);
      this.state = {
        loader:false,
        name:null,
        mail:null,
        phone:null,
        subject:null,
        message:null
      };
  }
  gonderMesaj(){
    if(!this.state.name){
      Alert.alert("Bilgilendirme","Lütfen adınızı yazınız",[
        {text: 'Kapat', onPress: () => null}
      ]);
    }
    else if(!this.state.mail){
        Alert.alert("Bilgilendirme","Lütfen mail adresinizi yazınız",[
          {text: 'Kapat', onPress: () => null}
        ]);
    }
    else if(!this.state.subject){
        Alert.alert("Bilgilendirme","Lütfen mesaj konusunu yazınız",[
          {text: 'Kapat', onPress: () => null}
        ]);
    }
    else if(!this.state.message){
        Alert.alert("Bilgilendirme","Lütfen mesajınızı yazınız",[
          {text: 'Kapat', onPress: () => null}
        ]);
    }
    else{
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
                  p : 'start',
                  s : 2,
                  userid: global.userid,
                  token: global.token,
                  phone:this.state.phone,
                  name:this.state.name,
                  mail:this.state.mail,
                  subject:this.state.subject,
                  message:this.state.message
                })

            }).then((response) => response.json()).then((jr) =>
            {
              if(jr["status"] == "1"){
                Alert.alert("Bilgilendirme",jr.message,[
                  {text: 'Kapat', onPress: () => null}
                ]);
                 this.props.navigation.goBack();
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
  render() {
    return (
      <Container style={styles.anasayfaGenel, {backgroundColor:'#fff'}}>
      {
        this.state.loader?
<View style={styles.indicator}>
<Spinner color='green' />
</View>:null
}
      <View style={styles.headerview}>
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
        <Content>
        <View style={{marginTop:110, marginLeft:10}}>
        <Text style={styles.pagetitle}>Yardım & Destek</Text>
          <View style={styles.destekform}>
          <Form style={styles.kayitform}>
            <Item  style={styles.inputitem}>
              <Icon style={styles.destekicons} type="Ionicons" active name="person" />
              <Input style={styles.inputdestek} placeholder="Ad, Soyad*"  onChangeText={ (text) => this.setState({ name: text })}/>
            </Item>
            <Item  style={styles.inputitem}>
              <Icon style={styles.destekicons} type="Ionicons" active name="call" />
              <Input style={styles.inputdestek} placeholder="Telefon Numarası" maxLength={10} keyboardType="numeric" onChangeText={ (text) => this.setState({ phone: text })}/>
            </Item>
            <Item  style={styles.inputitem}>
              <Icon style={styles.destekicons} type="Ionicons" active name="mail" />
              <Input style={styles.inputdestek} placeholder="E-Mail*" onChangeText={ (text) => this.setState({ mail: text })} />
            </Item>
            <Item style={styles.inputitem}>
              <Icon style={styles.destekicons} type="Ionicons" active name="information-circle" />
              <Input style={styles.inputdestek} placeholder="Destek Konusu*" onChangeText={ (text) => this.setState({ subject: text })} />
            </Item>
            <Item style={styles.inputitem}>
              <Icon style={styles.destekicons} type="Ionicons" active name="help-circle" />
              <Textarea rowSpan={5} style={styles.inputdestektextarea} placeholder="Destek Talebiniz*" onChangeText={ (text) => this.setState({ message: text })} />
            </Item>
            <Button style={{backgroundColor:"#F39200",flex:1, width:'100%', marginTop:10,justifyContent: 'center'}} onPress={()=>this.gonderMesaj()}>
              <Text>Gönder</Text>
            </Button>
          </Form>
          </View>
        </View>
        </Content>
      </Container>
    );
  }
}

export default destek;
