import 'package:attendancewithqr/database/db_helper.dart';
import 'package:attendancewithqr/model/attendance.dart';
import 'package:attendancewithqr/utils/strings.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:rflutter_alert/rflutter_alert.dart';

import '../utils/utils.dart';

class ReportPage extends StatefulWidget {
  const ReportPage({super.key});

  @override
  ReportPageState createState() => ReportPageState();
}

class ReportPageState extends State<ReportPage> {
  DbHelper dbHelper = DbHelper();
  Future<List<Attendance>>? attendances;
  String? dateFromVal, dateToVal;
  int? totalData = 0;

  /// Global key scaffold
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  TextEditingController dateFrom = TextEditingController();
  TextEditingController dateTo = TextEditingController();

  @override
  void initState() {
    super.initState();
    getData();
  }

  /// Init filter by date
  DateTime selectedDateFrom = DateTime.now();
  DateTime selectedDateTo = DateTime.now();

  Future<void> _selectTheFrom(BuildContext context) async {
    final picked = await Utils().selectDate(
      context,
      selectedDateFrom,
      DateTime(1980),
      selectedDateFrom,
    );
    if (picked != null && picked != selectedDateFrom) {
      setState(() {
        dateFrom.text = DateFormat('yyyy-MM-dd').format(picked);
        dateFromVal = DateFormat('yyyy-MM-dd').format(picked);
        dateToVal = null;
      });
    }
  }

  Future<void> _selectTheTo(BuildContext context) async {
    if (dateFromVal == null) {
      setState(() {
        Utils().showAlertDialog(
          reportFilterDateFromInputEmpty,
          "info",
          AlertType.info,
          _scaffoldKey,
          isAnyButton: true,
        );
      });
    }

    final picked = await Utils().selectDate(
      context,
      DateTime.parse(dateFromVal!),
      DateTime.parse(dateFromVal!),
      selectedDateTo,
    );
    if (picked != null && picked != selectedDateTo) {
      setState(() {
        dateTo.text = DateFormat('yyyy-MM-dd').format(picked);
        dateToVal = DateFormat('yyyy-MM-dd').format(picked);
        _getDataFilterByDate();
      });
    }
  }

  Future<void> getData() async {
    if (mounted) {
      setState(() {
        attendances = dbHelper.getAttendances();
      });
    }
  }

  Future<void> _getDataFilterByDate() async {
    setState(() {
      if (dateFromVal != null && dateToVal != null) {
        attendances = dbHelper.filterByDateAttendances(dateFromVal, dateToVal);
        _getTotalDataLength().then((value) {
          setState(() {
            totalData = value;
          });
        });
      }
    });
  }

  Future<int> _getTotalDataLength() {
    return attendances!.then((value) {
      return value.length;
    });
  }

  /// Text form for filter by date
  InkWell buildInkWellDate(
    BuildContext context,
    TextEditingController dateCtl, {
    required bool isDateFrom,
  }) {
    return InkWell(
      onTap: () {
        /// Below line stops keyboard from appearing
        FocusScope.of(context).requestFocus(FocusNode());

        /// Show Date Picker Here
        isDateFrom ? _selectTheFrom(context) : _selectTheTo(context);
      },
      child: IgnorePointer(
        child: TextFormField(
          style: GoogleFonts.quicksand(color: Colors.white),
          controller: dateCtl,
          decoration: InputDecoration(
            labelText: isDateFrom ? reportFilterDateFrom : reportFilterDateTo,
            labelStyle: const TextStyle(color: Colors.white),
            prefixIcon: const Icon(
              Icons.calendar_today,
              color: Colors.white,
            ),
            fillColor: Colors.white,
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(25.0),
              borderSide: const BorderSide(
                color: Colors.blue,
              ),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(25.0),
              borderSide: const BorderSide(
                color: Colors.white,
                width: 2.0,
              ),
            ),
          ),

          /// ignore: missing_return
          validator: (e) {
            String? message;
            if (e!.isEmpty) {
              message = reportFilterInputEmpty;
            }
            return message;
          },
          onSaved: (e) {
            if (isDateFrom) dateFromVal = e;
            if (!isDateFrom) dateToVal = e;
          },
        ),
      ),
    );
  }

  SingleChildScrollView dataTable(List<Attendance> attendances) {
    totalData = attendances.length;

    return SingleChildScrollView(
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: DataTable(
          columns: const [
            DataColumn(
              label: Text(
                reportName,
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
            DataColumn(
              label: Text(
                reportDate,
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
            DataColumn(
              label: Text(
                reportTime,
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
            DataColumn(
              label: Text(
                reportType,
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
            DataColumn(
              label: Text(
                reportLocation,
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
          ],
          rows: attendances
              .map(
                (attendance) => DataRow(
                  cells: [
                    DataCell(
                      Text(attendance.date!),
                    ),
                    DataCell(
                      Text(attendance.time!),
                    ),
                    DataCell(
                      Text(attendance.type!),
                    ),
                    DataCell(
                      Text(attendance.location!),
                    ),
                  ],
                ),
              )
              .toList(),
        ),
      ),
    );
  }

  Expanded list() {
    return Expanded(
      child: FutureBuilder(
        future: attendances,
        builder: (context, snapshot) {
          if (!snapshot.hasData && null == snapshot.data) {
            return const Center(child: Text(reportNoData));
          }

          if (snapshot.hasData) {
            totalData = 0;
            _getTotalDataLength();
            return dataTable(snapshot.data!);
          }

          return const CircularProgressIndicator();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      appBar: AppBar(
        title: const Text(reportTitle),
      ),
      body: Column(
        mainAxisSize: MainAxisSize.min,
        children: <Widget>[
          Container(
            decoration: BoxDecoration(
              color: const Color(0xFF07074E),
              borderRadius: const BorderRadius.all(Radius.circular(12)),
              boxShadow: [
                BoxShadow(
                  color: Colors.grey.withOpacity(0.6),
                  spreadRadius: 5,
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Padding(
              padding: const EdgeInsets.all(15.0),
              child: Column(
                children: [
                  Text(
                    reportFilterByTitle,
                    style: GoogleFonts.quicksand(
                      color: Colors.white,
                      fontSize: 16.0,
                    ),
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  buildInkWellDate(context, dateFrom, isDateFrom: true),
                  const SizedBox(
                    height: 10,
                  ),
                  buildInkWellDate(context, dateTo, isDateFrom: false),
                  const SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Text(
                        reportFilterTotal,
                        style: GoogleFonts.quicksand(
                          color: Colors.white,
                          fontSize: 16.0,
                        ),
                      ),
                      FutureBuilder<dynamic>(
                        future: attendances,
                        builder: (context, snapshot) {
                          if (snapshot.hasData) {
                            return Text(
                              snapshot.data.length.toString(),
                              style: GoogleFonts.quicksand(
                                color: Colors.white,
                                fontSize: 16.0,
                              ),
                            );
                          }

                          return const CircularProgressIndicator();
                        },
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(
            height: 20,
          ),
          list(),
        ],
      ),
    );
  }
}
