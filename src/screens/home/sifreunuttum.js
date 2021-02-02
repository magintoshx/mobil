import React, { Component } from "react";
import {Image, View, Alert} from "react-native";
import {Container,Spinner, Header, Title, Content, Button, Left, Right, Body, Icon, H1, Accordion, Form, Item, Input, ListItem, CheckBox, Text} from "native-base";
import styles from "./styles";

const logo = require("../../../assets/logo.png");

class sifreunuttum extends Component {
  constructor(props) {
      super(props);
      this.state = {
        loader:false, phone:null
      };
  }

kodGonder(){
  if(!this.state.phone){
    Alert.alert("Bilgilendirme","Lütfen telefon numaranızı yazınız",[
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
                s : 5,
                phone:this.state.phone
              })

          }).then((response) => response.json()).then((jr) =>
          {
            if(jr["status"] == "1"){
              Alert.alert("Bilgilendirme",jr.message,[
                {text: 'Kapat', onPress: () => null}
              ]);
                global.sifresmsphone = this.state.phone;
               this.props.navigation.navigate("sifreunuttumadim2");
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
      <Container>
      {
        this.state.loader?
        <View style={styles.indicator}>
        <Spinner color='green' />
        </View>:null
        }
        <Header barStyle="kayitbar" androidStatusBarColor='#ffffff'>
        <Left style={{ flex: 1 }}>
          <Button style={styles.headeringeribtn} transparent onPress={() => this.props.navigation.goBack()}>
            <Icon style={styles.headeringeriicon} name="arrow-back" />
          </Button>
        </Left>
          <Body>
            <Title>Kayıt Ol</Title>
          </Body>
          <Right />
        </Header>
        <Content padder style={{ backgroundColor: "white" }}>
          <View style={styles.kayitContainer}>
            <Image source={logo} style={styles.logo} resizeMode="contain" ></Image>
            <Form style={styles.kayitform}>
              <Item  style={styles.inputitem}>
                <Icon style={styles.loginicons} type="Ionicons" active name="call" />
                <Input placeholder="535*******"  maxLength={10} keyboardType="phone-pad" onChangeText={ (text) => this.setState({ phone: text })}/>
              </Item>
            </Form>
            <Button style={styles.girisbtn} onPress={()=>this.kodGonder()}>
              <Text style={styles.txt} uppercase={false}>Sms Kodumu Gönder</Text>
            </Button>
          </View>
        </Content>
      </Container>
    );
  }
}

export default sifreunuttum;
