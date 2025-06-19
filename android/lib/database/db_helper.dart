import 'dart:async';

import 'package:attendancewithqr/model/attendance.dart';
import 'package:attendancewithqr/model/settings.dart';
import 'package:path_provider/path_provider.dart';
import 'package:sqflite/sqflite.dart';

/// DB Helper
class DbHelper {
  /// Db name file
  String dbName = 'attendance.db';

  /// table name
  /// Table settings
  String tableSettings = 'settings';

  /// Table attendance
  String tableAttendance = 'attendances';

  factory DbHelper() {
    return DbHelper._createObject();
  }

  DbHelper._createObject();

  Future<Database> initDb() async {
    /// Init name and directory of DB
    final directory = await getApplicationDocumentsDirectory();
    final path = directory.path + dbName;

    /// Create, read databases
    final todoDatabase = openDatabase(path, version: 1, onCreate: _createDb);
    return todoDatabase;
  }

  /// Create the table
  Future<void> _createDb(Database db, int version) async {
    /// Table for settings
    await db.execute('''
      CREATE TABLE $tableSettings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        url TEXT,
        key TEXT
        )
    ''');

    /// Table for Attendance
    await db.execute('''
      CREATE TABLE $tableAttendance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        date TEXT,
        time TEXT,
        location TEXT,
        type TEXT
        )
    ''');
  }

  Future<Database?> get database {
    return initDb();
  }

  //--------------------------- Settings --------------------------------------
  /// Check there is any data
  Future<int?>? countSettings() async {
    final db = (await database)!;
    final count = Sqflite.firstIntValue(
      await db.rawQuery('SELECT COUNT(*) FROM $tableSettings'),
    );
    return count;
  }

  /// Insert new data
  Future<int> newSettings(Settings newSettings) async {
    final db = (await database)!;
    final result = await db.insert(tableSettings, newSettings.toJson());
    return result;
  }

  /// Get the data by id
  Future<Settings?>? getSettings(int id) async {
    final db = (await database)!;
    final res = await db.query(tableSettings, where: "id = ?", whereArgs: [id]);
    return res.isNotEmpty ? Settings.fromJson(res.first) : null;
  }

  /// Update the data
  Future<int> updateSettings(Settings updateSettings) async {
    final db = (await database)!;
    final result = await db.update(
      tableSettings,
      updateSettings.toJson(),
      where: "id = ?",
      whereArgs: [updateSettings.id],
    );
    return result;
  }

  //--------------------------- Attendance -------------------------------------

  /// Insert new data attendance
  Future<int> newAttendances(Attendance newAttendance) async {
    final db = (await database)!;
    final result = await db.insert(tableAttendance, newAttendance.toJson());
    return result;
  }

  /// Get All attendance
  Future<List<Attendance>> getAttendances() async {
    final db = (await database)!;
    final List<Map> maps = await db.rawQuery(
      "SELECT * FROM $tableAttendance ORDER BY date(date) DESC, time(time) DESC",
    );
    final List<Attendance> employees = [];
    if (maps.isNotEmpty) {
      for (int i = 0; i < maps.length; i++) {
        employees.add(Attendance.fromJson(maps[i] as Map<String, dynamic>));
      }
    }
    return employees;
  }

  /// Filter attendance
  Future<List<Attendance>> filterByDateAttendances(
    String? dateFrom,
    String? dateTo,
  ) async {
    final db = (await database)!;
    final List<Map> maps = await db.rawQuery(
      "SELECT * FROM $tableAttendance WHERE date BETWEEN '$dateFrom' AND '$dateTo'  ORDER BY date(date) DESC, time(time) DESC",
    );
    final List<Attendance> employees = [];
    if (maps.isNotEmpty) {
      for (int i = 0; i < maps.length; i++) {
        employees.add(Attendance.fromJson(maps[i] as Map<String, dynamic>));
      }
    }
    return employees;
  }
}
