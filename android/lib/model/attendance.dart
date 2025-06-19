import 'dart:convert';

String attendanceToJson(List<Attendance> data) =>
    json.encode(List<dynamic>.from(data.map((x) => x.toJson())));

class Attendance {
  int? id;
  String? date;
  String? time;
  String? location;
  String? type;

  Attendance({this.id, this.date, this.time, this.location, this.type});

  factory Attendance.fromJson(Map<String, dynamic> json) => Attendance(
        id: int.parse(json["id"].toString()),
        date: json["date"].toString(),
        time: json["time"].toString(),
        location: json["location"].toString(),
        type: json["type"].toString(),
      );

  Map<String, dynamic> toJson() => {
        "id": id,
        "date": date,
        "time": time,
        "location": location,
        "type": type,
      };
}
