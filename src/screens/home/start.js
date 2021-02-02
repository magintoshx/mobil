import React, { Component } from "react";
import {Image, View, Alert,AsyncStorage} from "react-native";
import {Container,Spinner, Header, Title, Content, Button, Left, Right, Body, Icon, H1, Accordion, Form, Item, Input, ListItem, CheckBox, Text} from "native-base";
import styles from "./styles";
import SplashScreen from 'react-native-splash-screen';
import {NavigationActions, StackActions} from 'react-navigation';

const logo = require("../../../assets/logo.png");

class start extends Component {
  constructor(props) {
      super(props);
  }
componentWillMount(){
  this.getValue();
}

geriSil(){

  const resetToHome = NavigationActions.reset({
    index: 0,
    actions: [
      NavigationActions.navigate({ routeName: 'Drawer'})
    ],
    key: null
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
           //this.geriSil();
		   let resetToHome = StackActions.reset({
                        index: 0,
                        actions: [NavigationActions.navigate({routeName: 'Drawer'})],
                        key: null,
                    });
                    this.props.navigation.dispatch(resetToHome);
         }else{
			  let resetToHome = StackActions.reset({
                        index: 0,
                        actions: [NavigationActions.navigate({routeName: 'Home'})],
                        key: null,
                    });
                    this.props.navigation.dispatch(resetToHome);
         }
        SplashScreen.hide();
      } catch (error) {
        Alert.alert(error);
    }
 }

  render() {
    return (
      <Container style={{backgroundColor:'#fff'}}>
      </Container>
    );
  }
}

export default start;
