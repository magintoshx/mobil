import React, { Component } from "react";
import { ImageBackground, View, StatusBar,Image, Alert} from "react-native";
import { Container, Header, Spinner,Title, Content, Text, Button, Icon, Footer, FooterTab, Left, Right, Body, Badge,List, ListItem} from "native-base";
import moment from 'moment';
require('moment/locale/tr.js');

import styles from "./styles";
import { SwipeListView } from 'react-native-swipe-list-view';

const logo = require("../../../assets/logo.png");

class bildirim extends Component {
  constructor(props) {
      super(props);
      this.state = {
        bildirims:null,
        loader:true
      };
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
                  p : 'start',
                  s : 8,
                  userid:global.userid,
                  token:global.token
                })

            }).then((response) => response.json()).then((jr) =>
            {
              if(jr["status"] == 1){
                this.setState({
                  bildirims:jr.message
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
                      p : 'start',
                      s : 12,
                      userid:global.userid,
                      token:global.token,
                      id:id
                    })

                }).then((response) => response.json()).then((jr) =>
                {
                  if(jr["status"] == 1){
                    let newData = [...this.state.bildirims];
                    newData.splice(rowKey, 1);
                    this.setState({ bildirims: newData });
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
  render() {

    return (<Container style={styles.anasayfaGenel, {backgroundColor:'#fff'}}>
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
      <Right style={{ flex: 1 }}>

      </Right>
    </Header>
    </View>
      <Content>
      <View style={{marginTop:110}}>
      <Text style={styles.pagetitle}>Bildirimler</Text>

        <View style={styles.pincswipe}>
        {this.state.bildirims?
		 <SwipeListView
            leftOpenValue={75}
            rightOpenValue={-75}
            data={this.state.bildirims}
            renderItem={(data, rowMap) =>
       
            <ListItem
            style={styles.swiperow}
            >
            <View style={styles.swipegecmis}>
            <View style={styles.sgcenter, {flex:0.7}}>
              <Text style={{fontSize:18,alignSelf:'flex-start', paddingLeft:15}}>{data.item.text}</Text>
            </View>
            <View style={styles.sgright, {flex:0.3}}>
              <Text style={{marginBottom:5,alignSelf:'flex-end'}}>{moment(data.item.created_at).format("L")}</Text>
             <Text style={{alignSelf:'flex-end', color:'#CDD0D2'}}>{moment(data.item.created_at).format("LT")}</Text>
            </View>
            </View>

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
        />
        :<Text style={styles.hicyok}>Hiç bildirim yok</Text>
      }
        </View>
      </View>
      </Content>
    </Container>
    );
  }
}

export default bildirim;
