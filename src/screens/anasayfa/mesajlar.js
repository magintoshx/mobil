import React, { Component } from "react";
import { ImageBackground, View, StatusBar,Image,TouchableOpacity, Alert } from "react-native";
import { Container, Header, Title, Content, Text, Button, Icon, Footer, FooterTab, Left, Right, Body, Badge,List, ListItem,Thumbnail,Spinner} from "native-base";
import moment from 'moment';
require('moment/locale/tr.js');
import styles from "./styles";
import { SwipeListView } from 'react-native-swipe-list-view';

const logo = require("../../../assets/logo.png");

class mesajlar extends Component {
  constructor(props) {
      super(props);
      _getMessageData = this._getMessageData.bind(this);
      this.state = {
        messages:null,
        loader:true
      };
    }
    _getMessageData = (id)=> {
      global.chatid = id;
      this.props.navigation.navigate("mesajlas");
    }
    componentWillMount(){
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
                  s : 1,
                  userid:global.userid,
                  token:global.token
                })

            }).then((response) => response.json()).then((jr) =>
            {
              if(jr["status"] == 1){
                this.setState({
                  messages:jr.message
                })
              }else{
                Alert.alert("Bilgilendirme",jr.message,[
                  {text: 'Kapat', onPress: () => null}
                ]);
              }
              this.setState({ loader : false});
            }).catch((error) =>
            {
                Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                  {text: 'Kapat', onPress: () => null}
                ]);
                this.setState({ loader : false});
            });
    }
	    closeRow(rowMap, rowKey) {
        if (rowMap[rowKey]) {
            rowMap[rowKey].closeRow();
        }
    }
    deleteRow(rowMap,rowKey, id) {
		  this.closeRow(rowMap, rowKey);
      Alert.alert("Bilgilendirme","Bu sohbeti tamamen silmek istediğinize emin misiniz?",[
        {text: 'Evet', onPress: () => {
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
                      s : 2,
                      userid:global.userid,
                      token:global.token,
                      id:id
                    })

                }).then((response) => response.json()).then((jr) =>
                {
                  if(jr["status"] == 1){
                    let newData = [...this.state.messages];
                    newData.splice(rowKey, 1);
                    this.setState({ messages: newData });

                  }else{
                    Alert.alert("Bilgilendirme",jr.message,[
                      {text: 'Kapat', onPress: () => null}
                    ]);
                  }
                  this.setState({ loader : false});
                }).catch((error) =>
                {
                    Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                      {text: 'Kapat', onPress: () => null}
                    ]);
                    this.setState({ loader : false});
                });
        }},
        {text: 'Kapat', onPress: () => null}
      ]);

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
        <View style={{marginTop:110}}>
        <Text style={styles.pagetitle}>Mesajlar</Text>
          <View style={styles.pincswipe}>
          <List>
          {
            this.state.messages?
			 <SwipeListView
			 disableRightSwipe
            leftOpenValue={0}
            rightOpenValue={-75}
            data={this.state.messages}
            renderItem={(data, rowMap) =>
           
                <ListItem
                avatar
                style={styles.swiperow}
                >
                <TouchableOpacity onPress={this._getMessageData.bind(this,data.item.id)} style={{
                  flex:1,
                  flexDirection:'row'
                }}>
                <Left>
				{data.item.driver &&
				<Thumbnail source={{uri:global.sunucu+"app/data/img/"+data.item.driver.avatar}} style={{marginLeft:15, marginTop:15, marginBottom:15}} /> }
                 </Left>
                 <Body style={{    flex: 1,
    justifyContent: 'center',
	borderBottomWidth:0
}}>
                   <Text>{data.item.driver?data.item.driver.name:"Bilinmiyor"}</Text>
                   <Text note numberOfLines={1} ellipsizeMode="tail">{data.item.sonmesaj?data.item.sonmesaj.text:"Bilinmiyor"}</Text>
                 </Body>
                 <Right style={{borderBottomWidth:0}}>
                   <Text note>{moment(data.item.created_at).format('LT')}</Text>
                 </Right>
                 </TouchableOpacity>
                </ListItem>}
				 renderHiddenItem={ (data, rowMap) => (
                <View>
				 <Button full style={{
                 width:75,
						height:104,
						position:"absolute",
						right:0,
						justifyContent:"center",
						alignItems:"center",
						flex:1,		
                  backgroundColor:'#313543'
				  
                }} onPress={_ => this.deleteRow(rowMap, data.item.key, data.item.id)}>
                  <Icon active name="close" />
                </Button>
				
                </View>
            )}
          
            />:<Text style={styles.hicyok}>Hiç mesajınız yok</Text>
    }
      </List>
          </View>
        </View>
        </Content>
      </Container>
    );
  }
}

export default mesajlar;
