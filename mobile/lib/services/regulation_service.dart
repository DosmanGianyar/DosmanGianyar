import '../models/school_regulation.dart';
import 'api_client.dart';

class RegulationService {
  RegulationService._();

  static Future<List<RegulationGroup>> fetchAll() async {
    final body = await ApiClient.get('/school-regulations');
    final list = body['regulations'] as List<dynamic>;
    return list.map((e) => RegulationGroup.fromJson(e as Map<String, dynamic>)).toList();
  }
}
