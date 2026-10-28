<?php

use App\Support\CodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The three standard policy documents (consent, drug policy, appointment
 * policy) used to be hardcoded Blade partials with no way for a counselor
 * to edit them. Seeding them as real `policies` rows lets the existing
 * counselor Policies CRUD screen manage them like any other policy — see
 * CounselorPolicyController, which still treats these three slugs as
 * un-deletable since the rest of the app depends on them existing.
 */
return new class extends Migration
{
    public function up(): void
    {
        $documents = [
            'consent' => [
                'title' => 'Client Disclosure and Consent Form',
                'description' => 'Official CCPW counseling consent document',
                'body' => $this->consentBody(),
            ],
            'drugs' => [
                'title' => 'Zero Tolerance on Drugs',
                'description' => 'Campus drug policy, sanctions, and privacy',
                'body' => $this->drugsBody(),
            ],
            'appointments' => [
                'title' => 'Appointments & Cancellation',
                'description' => 'Booking window, weekdays, and missed sessions',
                'body' => $this->appointmentsBody(),
            ],
        ];

        foreach ($documents as $slug => $doc) {
            if (DB::table('policies')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('policies')->insert([
                'policy_code' => CodeGenerator::make('POLICY'),
                'slug' => $slug,
                'title' => $doc['title'],
                'description' => $doc['description'],
                'body' => $doc['body'],
                'counselor_id' => null,
                'counselor_name' => 'CCPW (standard document)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('policies')->whereIn('slug', ['consent', 'drugs', 'appointments'])->delete();
    }

    private function consentBody(): string
    {
        return <<<'TEXT'
Welcome to the Center for Counseling and Psychological Well-being (CCPW) at Beaconhouse National University (BNU). Our counseling services aim to support individuals in gaining insight, developing coping strategies, and improving overall well-being in a safe, respectful, and confidential space. All members of the BNU community, including students, faculty, and staff may seek counseling for many reasons, including managing stress, navigating personal or professional challenges, exploring thoughts and emotions, or addressing interpersonal, academic, or work-related concerns.

Counseling works best when there is clear communication and mutual understanding. This form explains what clients can expect from counseling, the limits of confidentiality, professional boundaries, and the rights and responsibilities of individuals receiving counseling services. Please read it carefully and feel free to ask any questions before signing.

Confidentiality
Information shared during counseling sessions is treated as confidential and will not be disclosed outside the counseling setting without the client's permission. However, there are some exceptions where counselors may be legally or ethically required to disclose the necessary information:
- If there is a serious risk of harm to you or someone else, or if you disclose plans to commit a crime or dangerous act.
- If there is suspected or disclosed abuse involving a child, elderly person, or someone with a disability.
- If there are repeated violations of university policies or involvement in serious misconduct, and the university formally requests information (only the necessary and relevant details will be shared).
- Counseling records are kept for therapeutic purposes and are not intended for medico-legal use. However, in rare cases, information may be disclosed if required by law or court order.

In some cases, counselors may also consult with other mental health professionals (without revealing the client's identity) for professional guidance to ensure the best support possible. In situations where safety is a concern, appropriate authorities or an emergency contact may be notified if necessary.

Services Offered
Counseling services include individual sessions tailored to each client's needs. Counselors may draw from a variety of evidence-based therapeutic approaches, such as Cognitive Behavioral Therapy (CBT), Dialectical Behavior Therapy (DBT), Emotion-Focused Therapy (EFT), and Acceptance and Commitment Therapy (ACT). All sessions are delivered with a trauma-informed approach, ensuring emotional safety and respect for each client's individual experiences.

Professional Boundaries
Counselors maintain clear professional boundaries to ensure that counseling remains a safe and focused space. These boundaries include:
- Avoiding dual relationships such as friendships, romantic involvement, or social engagements during or after counseling.
- Limiting communication to official and professional channels (e.g., scheduled sessions, university email, or telephone).
- Maintaining respectful and brief interactions if encountered in campus or public settings.
- Not accepting personal gifts or engaging in personal social media connections.
- Avoiding physical contact unless therapeutically appropriate and with explicit consent.

These boundaries help ensure a safe, ethical, and focused counseling experience.

Limitations of Online/Remote Counseling (if applicable)
In the case of remote or online sessions, clients are expected to ensure a quiet and private space. While efforts are made to ensure confidentiality, the university is not responsible for technical failures or privacy breaches arising from third-party platforms.

Missed Appointments/Cancellation Policy
If a client is unable to attend a scheduled session, they are required to inform the counselor at least 24 hours in advance. Repeated missed appointments without prior notice or a request to reschedule may result in the appointment slot being offered to another client.

Client's Rights and Responsibilities
Individuals receiving counseling at BNU have the right to:
- Receive confidential, respectful and non-discriminatory care.
- Be informed and actively involved in the counseling process.
- Request a referral to a different professional, or discontinue counseling at any time.

Clients are expected to:
- Attend sessions on time.
- Be honest and open to the counseling process.
- Inform their counselor if they are feeling unsafe or experiencing a crisis.
- Respect the counseling boundaries and communicate concerns.

Acknowledgement and Informed Consent
I understand that the counseling services provided are intended for support, guidance, and personal development and are not a substitute for clinical treatment, diagnosis, or medical care. If at any point I require emergency or psychiatric support, I understand that I will be referred to appropriate services. If I am referred to an external provider, service, or organization, I understand that the CCPW at BNU does not assume responsibility for the practices, quality, or outcomes of these third-party services. A referral does not constitute endorsement. By signing below, I acknowledge that:
- Participation in counseling is voluntary
- I may terminate these services at any time
- I may need to discuss material of an upsetting nature as part of exploring personal concerns and working toward meaningful change
- I understand it cannot be guaranteed that I will feel better after completion of treatment
TEXT;
    }

    private function drugsBody(): string
    {
        return <<<'TEXT'
The zero-tolerance policy on drugs at BNU reflects the institution's dedication to creating a safe and conducive learning environment for all its members. This approach is in line with broader efforts to promote responsible behavior among students and faculty. By clearly stating its stance against the use, possession, or distribution of illegal drugs, BNU sends a strong message that drug abuse will not be tolerated within its campus community.

This policy serves several important purposes:
- Safety and Well-being: The primary aim is to protect the safety and well-being of students, faculty, and staff. Drug abuse can lead to a variety of physical and mental health problems, and its presence on campus can increase the risk of accidents, violence, and other harmful incidents. To address this, the protocol will incorporate random drug screening as a preventive measure. This initiative will not only deter substance use but also help identify individuals who may require support or intervention. Random screenings will be conducted in a fair, confidential, and non-discriminatory manner, ensuring that the process respects the privacy and dignity of all community members while prioritizing campus safety.
- Academic Focus: A drug-free campus environment allows students to concentrate on their academic pursuits without the distractions and potential negative influences associated with drug use. This supports the institution's commitment to academic excellence.
- Legal Compliance: Enforcing a zero-tolerance policy ensures that BNU complies with legal regulations regarding drug use. Violations of drug laws can have serious legal consequences, and the university has a responsibility to uphold the law.
- Community Values: By maintaining a drug-free campus, BNU reinforces its commitment to fostering a healthy and productive educational community. This sends a clear message about the values and standards expected of all members.
- Prevention and Education: The zero-tolerance approach also provides opportunities for education and prevention. BNU can offer resources, support, and programs to help students make informed decisions about substance use and seek help if needed.

Overall, BNU's zero-tolerance policy on drugs is a proactive step toward creating an environment that prioritizes the safety, well-being, and academic success of its community members while upholding legal standards. It aims to prevent substance-related problems, provide support and resources for those in need, and create a culture of responsibility and awareness regarding substance use and addiction issues.

The specific punishments for drug-related violations at BNU are based on the institution's policies, local/national laws, and the severity of the violation. The range of sanctions that may be applied in response to drug-related offenses include:
- Disciplinary Action: The university may initiate its own disciplinary process, which could result in consequences such as probation, suspension, or expulsion, depending on the seriousness of the offense. These actions are often taken in accordance with the university's code of conduct.
- Counseling or Treatment: In some cases, instead of or in addition to disciplinary measures, the university may offer counseling or drug treatment programs to students who have violated drug policies. The goal is to help students address underlying issues and make positive changes.
- Legal Consequences: If the drug violation also violates local or national laws, the university may report the incident to law enforcement authorities. Students involved may face criminal charges, fines, and potential imprisonment as determined by the legal system.
- Educational Programs: The university may require students who violate drug policies to complete educational programs related to drug awareness, prevention, or harm reduction as a part of their disciplinary or corrective actions.
- Loss of Privileges: Violators may lose certain privileges, such as eligibility for financial aid, participation in extracurricular activities, or access to campus facilities.
- Community Service: The university may require students to perform community service as a way to make amends for their actions.

Confidentiality and Privacy
Any student whose case is being handled by the drug committee will be assured that their privacy will be respected. He/she will be provided with information about his/her rights to privacy and confidentiality policies during the initial phase of case processing.

If a student wishes to have their information shared with specific individuals or agencies, they can do so by signing a release of information form. This allows the university to communicate with designated individuals about the student's treatment.

In case of external referrals, parents/guardians will be informed as per the university's policy.

The records of these cases will be kept completely confidential and only the relevant personnel will have access to the student's case details.

Note: Anything mentioned in this document does not prejudice the university's administrative decisions regarding disciplinary actions of any kind against those involved in drug use, facilitation and/or possession, repercussions may vary on a case-to-case basis according to the university rules and procedures. It's essential to note that the severity of the punishment often depends on factors such as the type and amount of drugs involved, any previous violations, and whether the violation involved distribution or only personal use. Additionally, details regarding drug policies and potential consequences are mentioned in student handbooks and/or codes of conduct, so students are aware of the rules and expectations. If you have concerns about drug-related policies and punishments at BNU, it's advisable to consult the university's official documents and, if necessary, seek guidance from the university's administration or legal experts to better understand the implications of such policies.
TEXT;
    }

    private function appointmentsBody(): string
    {
        return <<<'TEXT'
This policy outlines how appointments with the Center for Counseling and Psychological Well-being (CCPW) can be booked, rescheduled, and cancelled.

Booking Window
Appointments can only be booked up to 14 days in advance. This keeps the schedule current and ensures counselors can plan their availability effectively.

Available Days & Times
Sessions are available Monday through Friday, between 9:00 AM and 5:00 PM, in one-hour slots. Appointments are not available on weekends.

Missed Appointments / Cancellation Policy
If you are unable to attend a scheduled session, you are required to inform your counselor at least 24 hours in advance. Repeated missed appointments without prior notice or a request to reschedule may result in the appointment slot being offered to another student.

Rescheduling Policy
Each appointment can only be rescheduled once by the student. If you need to make further changes, or if you already have another active appointment, please contact your counselor directly to discuss your options.

One Active Appointment at a Time
You may only have one active (pending, accepted, or rescheduled) appointment at a time. A new appointment cannot be booked until your current one is completed, rejected, or cancelled.
TEXT;
    }
};
