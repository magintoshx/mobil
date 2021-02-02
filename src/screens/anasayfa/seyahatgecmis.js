import React, { Component } from "react";
import { ImageBackground, View, StatusBar,Image,TouchableOpacity, Alert} from "react-native";
import { Container, Header, Title,  Thumbnail,Content, Text, Button, Icon, Footer, FooterTab, Left, Right, Body, Badge,  Spinner,List, ListItem} from "native-base";

import styles from "./styles";

const logo = require("../../../assets/logo.png");
const launchscreenBg = require("../../../assets/launchscreen-bg.png");
const launchscreenLogo = require("../../../assets/logo-kitchen-sink.png");
const itemgecmisleft = require("../../../assets/itemgecmisleft.png");
import StarRating from 'react-native-star-rating';
import moment from 'moment';
import Modal from 'react-native-modalbox';
import ListView from 'deprecated-react-native-listview';
import { SwipeListView } from 'react-native-swipe-list-view';

require('moment/locale/tr.js');
class seyahatgecmis extends Component {
  constructor(props) {
      super(props);
      this.state = {
        lasttravels:null,
        loader:true,
        starCount: 3,
        puanlaId:null,
        driveravatar:null,
        drivername:null,
        drivercarplate:null
      };
    }
    puanla(id){
      this.setState({loader:true});
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
                  s : 14,
                  userid:global.userid,
                  token:global.token,
                  id:id
                })

            }).then((response) => response.json()).then((jr) =>
            {
              console.log(jr);
              if(jr["status"] == 1){
                this.refs.modal1.open();
                this.setState({
                  puanlaId:id,
                  drivername:jr.message.name,
                  driveravatar:jr.message.avatar,
                  drivercarplate:jr.message.car_plate,
				  loader : false
                });
              }else{
                Alert.alert("Bilgilendirme",jr.message,[
                  {text: 'Kapat', onPress: () => this.setState({ loader : false})}
                ]);
              }

            }).catch((error) =>
            {
                console.log(error);
				this.setState({ loader : false});
            });
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
                s : 4,
                userid:global.userid,
                token:global.token
              })

          }).then((response) => response.json()).then((jr) =>
          {
			  console.log(jr.message.gecmis);
            if(jr["status"] == 1){
              this.setState({
                avatar:jr.message.user.avatar?jr.message.user.avatar:"noavatar.png",
                username:jr.message.user.name,
                lasttravels:jr.message.gecmis
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

    Alert.alert("Bilgilendirme","Bu seyahat geçmişini silmek istediğinize emin misiniz?",[
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
                    p : 'start',
                    s : 11,
                    userid:global.userid,
                    token:global.token,
                    id:id
                  })

              }).then((response) => response.json()).then((jr) =>
              {
                if(jr["status"] == 1){
                  //rowMap[`${secId}${rowId}`].props.closeRow();
                  const newData = [...this.state.lasttravels];
                  newData.splice(rowKey, 1);
                  this.setState({ lasttravels: newData });

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

  onClose() {
 }

 onOpen() {
 }

 onClosingState(state) {
 }
 onStarRatingPress(rating) {
  this.setState({
    starCount: rating
  });
}
seyahatPuanla(){
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
              s : 13,
              userid:global.userid,
              token:global.token,
              s_id:this.state.puanlaId,
              srate:this.state.starCount
            })

        }).then((response) => response.json()).then((jr) =>
        {
          Alert.alert("Bilgilendirme",jr.message,[
            {text: 'Kapat', onPress: () => null}
          ]);
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
      <Modal
         style={[styles.modal, styles.modal3]}
         ref={"modal1"}
         position={"center"}
         onClosed={this.onClose}
         onOpened={this.onOpen}
         onClosingState={this.onClosingState}>
         <Thumbnail style={{marginBottom:10}} large source={this.state.driveravatar?{uri:global.sunucu+"app/data/img/"+this.state.driveravatar}:{uri:global.sunucu+"app/data/img/noavatar.png"}} />
         <Text>{this.state.drivername}</Text>
         <Text style={{marginBottom:10}}>{this.state.drivercarplate}</Text>
         <Text style={{textAlign:'center', alignSelf:'center', marginBottom:15}}>Seyahatinizi 1 ile 5 arasında puanlayabilirsiniz</Text>
         <StarRating
          disabled={false}
          maxStars={5}
          starSize={25}
          rating={this.state.starCount}
          emptyStarColor={'#CDD0D2'}
          fullStarColor={'#FFB900'}
          style={{marginTop:10,alignSelf:'flex-end'}}
          selectedStar={(rating) => this.onStarRatingPress(rating)}
        />

           <Button success onPress={() => {
             this.refs.modal1.close(); this.seyahatPuanla();
           }} style={{
             backgroundColor:'#F39200', width:'100%',
             textAlign:'center',
             alignSelf:'center',
             justifyContent:'center',
             marginTop:15
           }}><Text>Seyahati Puanla</Text></Button>
       </Modal>
        <Content>
        <View style={{marginTop:110}}>
        <Text style={styles.pagetitle}>Seyahat Geçmişi</Text>
          <View style={styles.pincswipe}>
          {this.state.lasttravels?
          <SwipeListView
            leftOpenValue={75}
            rightOpenValue={-75}
            data={this.state.lasttravels}
            renderItem={(data, rowMap) =>
              <ListItem
              disableLeftSwipe={(data.status==2 && data.star==0)?false:true}
              style={styles.swiperow}
              >
              <View style={styles.swipegecmis}>
                <View style={styles.sgleft}>
                  <Image source={itemgecmisleft}  style={{height:85, marginLeft:13}} resizeMode='contain'/>
                </View>
                <View style={styles.sgcenter}>
                  <Text numberOfLines={1} ellipsizeMode='tail' style={{fontSize:18, marginTop:3,alignSelf:'flex-start'}}>{data.item.fromtext}</Text>
                  <Text numberOfLines={1} ellipsizeMode='tail' style={{fontSize:18, marginTop:30,alignSelf:'flex-start'}}>{data.item.totext}</Text>
                </View>
                <View style={styles.sgright}>
                  <Text style={{marginBottom:10,alignSelf:'flex-end'}}>{moment(data.item.created_at).format('L')}</Text>
                  <StarRating
                   disabled={true}
                   maxStars={5}
                   rating={data.item.star}
                   starSize={17}
                   containerStyle={{width:100}}
                   emptyStarColor={'#CDD0D2'}
                   fullStarColor={'#F39200'}
                   style={{marginTop:10,alignSelf:'flex-end'}}
                 />
                 <Text style={{marginTop:10,alignSelf:'flex-end', color:'#CDD0D2'}}>{data.item.odenen?data.item.odenen:((data.item.km) * (data.item.money)).toFixed(1)}₺{data.item.status==3?<Text style={{color:'red'}}> - İptal</Text>:null}</Text>
                </View>
              </View>



              </ListItem>}
			  renderHiddenItem={ (data, rowMap) => (
                <View>
                    <Button style={{
						width:75,
						height:84,
						position:"absolute",
						left:0,
						justifyContent:"center",
						alignItems:"center",
						flex:1,
					backgroundColor:'#313543'
				  }} onPress={_ => this.deleteRow(rowMap, data.item.key, data.item.id)}>
					<Icon active name="close" />
				  </Button>
				  <Button  disabled={data.item.status == 3 || data.item.star > 0?true:false} danger
              onPress={this.puanla.bind(this,data.item.id)}
                style={{
					width:75,
					height:104,
					position:"absolute",
					right:0,
					justifyContent:"center",
						alignItems:"center",
                  backgroundColor:'#F39200'
                }}>
                <Icon active name="star-half" />
              </Button>
                </View>
            )}

          />:<Text style={styles.hicyok}>Hiç seyahat yok</Text>
        }

          </View>
        </View>
        </Content>
      </Container>
    );
  }
}

export default seyahatgecmis;
