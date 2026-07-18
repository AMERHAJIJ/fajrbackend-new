import pandas as pd
import numpy as np
from sklearn.ensemble import IsolationForest
import sys
import json

def analyze_student_behavior(data_json):
    """
    هذا التابع يستقبل بيانات الطلاب ويقوم بتحديد الشواذ (Anomalies)
    """
    # تحويل البيانات إلى DataFrame
    df = pd.DataFrame(json.loads(data_json))
    
    if df.empty:
        return json.dumps({"status": "error", "message": "No data provided"})

    # [شرح أكاديمي للمناقشة]:
    # قمنا بتحديد هذه الخصائص (Features) لتركز فقط على (السلوك) أثناء مشاهدة الفيديو
    # مثل: مدة المشاهدة، عدد التوقفات، ومغادرة التطبيق.
    # تعمدنا عدم إدراج (علامة الاختبار quiz_score) هنا، لأن الذكاء الاصطناعي يبحث عن 
    # (السلوك الغشاش/المتلاعب)، بينما العلامة تقيس (الفهم الأكاديمي). الطالب قد يركز 
    # جداً ولا يغش، لكنه يرسب لأن الأسئلة صعبة. دمج العلامة هنا سيظلم الطالب.
    features = [
        'watched_duration', 
        'pause_count', 
        'forward_skip_count', 
        'backward_skip_count', 
        'playback_rate', 
        'app_switch_count'
    ]
    
    # تحضير البيانات
    X = df[features]
    
    # تعريف نموذج Isolation Forest
    # contamination: هي نسبة الشواذ المتوقعة (مثلاً 10%)
    model = IsolationForest(contamination=0.1, random_state=42)
    
    # تدريب النموذج وتوقع النتائج
    # 1 تعني طبيعي، -1 تعني شاذ (Anomaly)
    df['anomaly_prediction'] = model.fit_predict(X)
    df['anomaly_score'] = model.decision_function(X)
    
    # تحويل النتائج إلى صيغة يفهمها Laravel
    results = df[['id', 'anomaly_prediction', 'anomaly_score']].to_dict(orient='records')
    
    return json.dumps(results)

if __name__ == "__main__":
    # في حال التشغيل من Laravel، سنستقبل البيانات كـ Argument
    if len(sys.argv) > 1:
        if sys.argv[1] == '--file' and len(sys.argv) > 2:
            with open(sys.argv[2], 'r', encoding='utf-8') as f:
                input_data = f.read()
            print(analyze_student_behavior(input_data))
        else:
            input_data = sys.argv[1]
            print(analyze_student_behavior(input_data))
    else:   
        print(json.dumps({"status": "waiting", "message": "Ready for analysis"}))
        
 
