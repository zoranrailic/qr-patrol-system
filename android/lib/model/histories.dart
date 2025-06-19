import 'dart:convert';

String historiesToJson(List<History> data) =>
    json.encode(List<dynamic>.from(data.map((x) => x.toJson())));

class History {
  int? id;
  String? name;

  History({
    this.id,
    this.name,
  });

  factory History.fromJson(Map<String, dynamic> json) => History(
    id: json["id"] != null ? int.tryParse(json["id"].toString()) : null,
    name: json["name"]?.toString(),
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "name": name,
  };
}
