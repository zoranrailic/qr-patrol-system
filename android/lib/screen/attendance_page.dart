import 'dart:async';

import 'package:attendancewithqr/model/attendance.dart';
import 'package:attendancewithqr/utils/strings.dart';
import 'package:barcode_scan2/barcode_scan2.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:geocoding/geocoding.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:progress_dialog_null_safe/progress_dialog_null_safe.dart';
import 'package:rflutter_alert/rflutter_alert.dart';
import 'package:trust_fall/trust_fall.dart';

import '../database/db_helper.dart';
import '../model/settings.dart';
import '../utils/utils.dart';

class AttendancePage extends StatefulWidget {
  final String? query;
  final String? title;

  const AttendancePage({super.key, this.query, this.title});

  @override
  AttendancePageState createState() => AttendancePageState();
}

class AttendancePageState extends State<AttendancePage> {
  /// Progress dialog
  late ProgressDialog pr;

  /// Database
  DbHelper dbHelper = DbHelper();

  /// Utils
  Utils utils = Utils();

  /// Model settings
  Settings? settings;

  /// Global key scaffold
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  /// String
  String? getUrl,
      getKey,
      _barcode = "",
      getQuery,
      getPath = 'api/attendance/apiSaveAttendance',
      mAccuracy;

  String? getQrId;
  bool? _isMockLocation, clickButton = false;

  /// Geolocation
  late Position _currentPosition;
  String? _currentAddress;
  final Geolocator geoLocator = Geolocator();
  late dynamic subscription;
  double setAccuracy = 200.0;

  @override
  void initState() {
    super.initState();
    _getCurrentLocation();
  }

  @override
  void dispose() {
    super.dispose();
  }

  /// Get latitude longitude
  void _getCurrentLocation() {
    subscription = Geolocator.getPositionStream().listen((position) {
      if (mounted) {
        setState(() {
          _currentPosition = position;
        });

        _getAddressFromLatLng(_currentPosition.accuracy);
      }
    });

    /// Check fake gps
    checkMockLoc();

    /// Do settings
    getSettings();
  }

  /// Checking Mock (fake GPS)
  Future<void> checkMockLoc() async {
    try {
      _isMockLocation = await TrustFall.canMockLocation;
    } catch (error) {
      if (kDebugMode) {
        print(error);
      }
    }
  }

  /// Get address
  Future<void> _getAddressFromLatLng(double accuracy) async {
    final strAccuracy = accuracy.toStringAsFixed(1);
    if (accuracy > setAccuracy) {
      mAccuracy = '$strAccuracy $attendanceNotAccurate';
    } else {
      mAccuracy = '$strAccuracy $attendanceAccurate';
    }
    try {
      final List<Placemark> p = await placemarkFromCoordinates(
        _currentPosition.latitude,
        _currentPosition.longitude,
      );

      final Placemark placeMark = p[0];

      // Proverimo svako polje pojedinačno kako bismo izbegli prazne vrednosti
      String name = placeMark.name ?? '';
      String street = placeMark.street ?? '';
      String locality = placeMark.locality ?? '';
      String subLocality = placeMark.subLocality ?? '';
      String administrativeArea = placeMark.administrativeArea ?? '';
      String subAdministrativeArea = placeMark.subAdministrativeArea ?? '';
      String postalCode = placeMark.postalCode ?? '';
      String country = placeMark.country ?? '';

      // Kreiranje liste sa validnim delovima adrese
      List<String> addressParts = [
        name,
        street,
        subLocality,
        locality,
        subAdministrativeArea,
        administrativeArea,
        postalCode,
        country,
      ];

      // Filtriramo prazne vrednosti
      addressParts.removeWhere((part) => part.isEmpty);

      if (mounted) {
        setState(() {
          _currentAddress = "$mAccuracy ${addressParts.join(', ')}.";
        });
      }
    } catch (e) {
      if (kDebugMode) {
        print("Greška prilikom dobijanja adrese: $e");
      }
    }
  }

  /// Get settings data
  Future<void> getSettings() async {
    final getSettings = await dbHelper.getSettings(1);
    setState(() {
      getUrl = getSettings!.url;
      getKey = getSettings.key;
    });
  }

  /// Send data post via http
  Future<void> sendData() async {
    // Proveravamo da li je adresa prazna pre slanja
    if (_currentAddress == null || _currentAddress!.isEmpty) {
      utils.showAlertDialog(
        "Lokacija nije dostupna, pokušajte ponovo.",
        "Greška",
        AlertType.error,
        _scaffoldKey,
        isAnyButton: true,
      );
      return;
    }

    /// Dodavanje podataka u mapu
    final Map<String, dynamic> body = {
      'location': _currentAddress,
      'key': getKey,
      'qr_id': getQrId,
      'q': getQuery,
    };

    try {
      final uri = utils.getRealUrl(getUrl!, getPath);
      final Dio dio = Dio();
      final FormData formData = FormData.fromMap(body);
      final response = await dio.post(uri, data: formData);

      final data = response.data;

      if (data['message'] == 'Success!') {
        final Attendance attendance = Attendance(
          date: data['date'].toString(),
          time: data['time'].toString(),
          location: _currentAddress, // Osiguravamo da upisujemo pravilnu adresu
          type: data['query'].toString(),
        );

        await insertAttendance(attendance);

        if (mounted) {
          await pr.hide();
          utils.showAlertDialog(
            "$attendanceShowAlert-$getQuery $attendanceSuccessMs",
            "Success",
            AlertType.success,
            _scaffoldKey,
            isAnyButton: true,
          );
        }
      } else {
        await pr.hide();
        utils.showAlertDialog(
          response.data.toString(),
          "Greška",
          AlertType.error,
          _scaffoldKey,
          isAnyButton: true,
        );
      }
    } catch (e) {
      await pr.hide();
      utils.showAlertDialog(
        "Greška prilikom slanja podataka: $e",
        "Greška",
        AlertType.error,
        _scaffoldKey,
        isAnyButton: true,
      );
    }
  }


  Future<void> insertAttendance(Attendance object) async {
    await dbHelper.newAttendances(object);
  }

  /// Scan the QR name of user
  Future<void> scan() async {
    try {
      final barcode = await BarcodeScanner.scan();

      /// The value of Qr Code
      if (barcode.rawContent != '') {
        setState(() {
          /// Show dialog
          pr.show();

          /// Get name from QR
          getQrId = barcode.rawContent;

          /// Sending the data
          sendData();
        });
      }

      ///-- Optional if you want to show message when user click back button or cancel button, then uncomment this code --
      /// else {
      ///   utils.showAlertDialog(
      ///       '$barcode_empty', "Error", AlertType.error, _scaffoldKey, true);
      /// }
    } on PlatformException catch (e) {
      if (e.code == BarcodeScanner.cameraAccessDenied) {
        _barcode = cameraPermission;
        utils.showAlertDialog(
          _barcode,
          "Warning",
          AlertType.warning,
          _scaffoldKey,
          isAnyButton: true,
        );
      } else {
        _barcode = '$barcodeUnknownError $e';
        utils.showAlertDialog(
          _barcode,
          "Error",
          AlertType.error,
          _scaffoldKey,
          isAnyButton: true,
        );
      }
    } catch (e) {
      _barcode = '$barcodeUnknownError : $e';
      if (kDebugMode) {
        print(_barcode);
      }
    }
  }

  /// This function is about checking if the user uses Mock (Fake GPS) to make attendance
  Future<void> checkMockIsNull() async {
    /// Check if user click button attendance
    if (clickButton!) {
      /// Check mock is already get status
      if (_isMockLocation == null) {
        Future.delayed(Duration.zero).then((value) {
          /// Check if pr is showing or not
          if (!pr.isShowing()) {
            pr.show();
            pr.update(
              progress: 50.0,
              message: checkMock,
              progressWidget: Container(
                padding: const EdgeInsets.all(8.0),
                child: const CircularProgressIndicator(),
              ),
              maxProgress: 100.0,
              progressTextStyle: const TextStyle(
                color: Colors.black,
                fontSize: 13.0,
                fontWeight: FontWeight.w400,
              ),
              messageTextStyle: const TextStyle(
                color: Colors.black,
                fontSize: 19.0,
                fontWeight: FontWeight.w600,
              ),
            );
          }
        });
      } else if (_isMockLocation == true) {
        /// If there user use mock location means uses fake gps
        /// Will show warning alert
        Future.delayed(Duration.zero).then((value) {
          /// Detect mock is true, mean user use fake gps
          setState(() {
            clickButton = false;
            if (pr.isShowing()) {
              pr.hide();
            }
          });

          utils.showAlertDialog(
            fakeGps,
            "warning",
            AlertType.warning,
            _scaffoldKey,
            isAnyButton: true,
          );
        });
      } else {
        /// If user not use fake gps
        Future.delayed(Duration.zero).then((value) async {
          setState(() {
            clickButton = false;
            if (pr.isShowing()) {
              pr.hide();
            }
          });

          /// If already get mock will continue show camera for scan the QR code
          return scan();
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    /// Show progress
    pr = ProgressDialog(
      context,
      isDismissible: false,
      type: ProgressDialogType.normal,
    );

    /// Style progress
    pr.style(
      message: attendanceSending,
      borderRadius: 10.0,
      backgroundColor: Colors.white,
      progressWidget: const CircularProgressIndicator(),
      elevation: 10.0,
      padding: const EdgeInsets.all(10.0),
      insetAnimCurve: Curves.easeInOut,
      progress: 0.0,
      maxProgress: 100.0,
      progressTextStyle: const TextStyle(
        color: Colors.black,
        fontSize: 13.0,
        fontWeight: FontWeight.w400,
      ),
      messageTextStyle: const TextStyle(
        color: Colors.black,
        fontSize: 19.0,
        fontWeight: FontWeight.w600,
      ),
    );

    /// Init the query
    getQuery = widget.query;

    /// Check if user use fake gps
    checkMockIsNull();

    return Scaffold(
      key: _scaffoldKey,
      appBar: AppBar(
        title: Text(widget.title!),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Text(
              '$attendanceAccurateInfo $mAccuracy $attendanceOnGps',
              style: GoogleFonts.quicksand(
                color: Colors.grey[800],
                fontSize: 14.0,
              ),
              textAlign: TextAlign.center,
            ),
            Container(
              margin: const EdgeInsets.all(20.0),
              width: double.infinity,
              height: 50.0,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF07074E),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18.0),
                  ),
                ),
                onPressed: () {
                  clickButton = true;
                },
                child: Text(
                  buttonScan,
                  style: GoogleFonts.quicksand(
                    color: Colors.white,
                    fontSize: 12.0,
                  ),
                ),
              ),
            ),
            Text(
              '$attendanceButtonInfo-$getQuery.',
              style: GoogleFonts.quicksand(color: Colors.grey, fontSize: 12.0),
            ),
          ],
        ),
      ),
    );
  }
}
