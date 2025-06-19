import 'package:attendancewithqr/utils/strings.dart';
import 'package:flutter/material.dart';
import 'package:rflutter_alert/rflutter_alert.dart';

class Utils {
  AlertStyle alertStyle = AlertStyle(
    animationType: AnimationType.fromTop,
    isCloseButton: false,
    descStyle: const TextStyle(fontSize: 18.0),
    animationDuration: const Duration(milliseconds: 400),
    alertBorder: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(0.0),
      side: const BorderSide(
        color: Colors.grey,
      ),
    ),
    titleStyle: const TextStyle(
      color: Colors.red,
    ),
  );

  /// Show snack bar
  void showAlertDialog(
    String? message,
    String title,
    AlertType alertType,
    GlobalKey<ScaffoldState> scaffoldKey, {
    required bool isAnyButton,
  }) {
    if (isAnyButton == true) {
      Alert(
        context: scaffoldKey.currentContext!,
        style: alertStyle,
        type: alertType,
        title: title,
        desc: message,
        buttons: [
          DialogButton(
            onPressed: () => Navigator.pop(scaffoldKey.currentContext!),
            width: 120,
            child: const Text(
              okText,
              style: TextStyle(color: Colors.white, fontSize: 20),
            ),
          ),
        ],
      ).show();
    } else {
      Alert(
        context: scaffoldKey.currentContext!,
        style: alertStyle,
        type: alertType,
        title: title,
        desc: message,
        buttons: [],
      ).show();
    }
  }

  /// Get the url, and this function will check if the last is any slash (/) or not
  String getRealUrl(String url, String? path) {
    String realUrl;
    final count = url.length - 1;
    final getLast = url[count];
    if (getLast == '/') {
      realUrl = '$url${path!}';
    } else {
      realUrl = '$url/${path!}';
    }
    return realUrl;
  }

  /// Show calender
  Future<DateTime?> selectDate(
    BuildContext context,
    DateTime init,
    DateTime start,
    DateTime last,
  ) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: init,
      firstDate: start,
      lastDate: last,
    );
    return picked;
  }
}
