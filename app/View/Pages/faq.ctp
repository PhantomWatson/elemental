<div class="page-header">
    <h1>
        <?php echo $title_for_layout; ?>
    </h1>
</div>

<?php
    $faq = array(
        'Is Elemental effective?' => 'effective',
        'How is Elemental delivered?' => 'delivered',
        'How do I bring Elemental to my school?' => 'booking',
        'How much does Elemental cost?' => 'cost',
        'What support do you offer for seminar registration and other logistics?' => 'support',
        'Who would make a good Elemental instructor?' => 'qualifications',
        'How does someone become an Elemental instructor?' => 'certification',
        'What are the personnel needs?' => 'personnel',
        'What are the equipment needs for Elemental?' => 'equipment',
        'Does Elemental incorporate best practices in the field?' => 'bestpractices',
        'Isn\'t self-defense programming just victim-blaming?' => 'victimblaming',
        'How does Elemental fit with other government-mandated and optional programming?' => 'fit'
    );
?>

<ul class="faq">
    <?php foreach ($faq as $question => $shortcut): ?>
        <li>
            <a href="#faq_<?php echo $shortcut; ?>">
                <?php echo $question; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<dl class="faq">
    <dt id="faq_effective">
        Is Elemental effective?
    </dt>
    <dd>
        <p>
            Yes. We have been collecting data on long-term outcomes
            for Elemental participants since program inception and have compared their
            outcomes to those of non-participants. Our results indicate that:
        </p>
        <blockquote>
            <p>
                Program participants demonstrate sustained changes in attitudes and beliefs that are empirically
                linked with assault risk through at least six months post-seminar.
            </p>
            <p>
                Assault rates among
                program participants are nearly 66% lower than for non-participants 6 months post seminar,
                net of the effects of demographic and other background factors.
            </p>
            <p>
                More than nine out
                of ten program participants used what they learned to protect themselves
                successfully when faced with a situation that was both sexually charged and
                awkward or dangerous.
            </p>
        </blockquote>
        <p>
            See our
            <?php echo $this->Html->link(
                'list of peer-reviewed publications and scholarly presentations',
                array('controller' => 'pages', 'action' => 'scholarly_work')
            ); ?>.
        </p>
    </dd>



    <dt id="faq_delivered">
        How is Elemental delivered?
    </dt>
    <dd>
        <p>
            Elemental is delivered as a face-to-face, media-driven seminar with time for interactive practice. Instructors and their assistants teach lessons and lead discussion about sexual assault, consent, effective verbal- and non-verbal communication, campus culture, gender-role socialization, environmental and alcohol awareness, and physical self-protection. Elemental was originally designed to be taught at a 6 hour seminar but it has since been modified to allow for other teaching models as well (original 6 hour version; summer bridge 9 hour version; 1 credit-hour semester version that takes place across 16 hours). Students who complete the program receive 1 year of access to online review materials that cover all of the verbal and physical techniques taught during the seminar.
        </p>
    </dd>



    <dt id="faq_booking">
        How do I bring Elemental to my school?
    </dt>
    <dd>
        <p>
            There are two ways to bring Elemental to your institution:
        </p>
        <ol>
            <li>
                Have instructors at your institution trained to offer Elemental to your students on an ongoing basis.
            </li>
            <li>
                Bring in a certified Elemental instructor to your institution on a per-class basis.
            </li>
        </ol>
    </dd>



    <dt id="faq_cost">
        How much does Elemental cost?
    </dt>
    <dd>
        <p>
            Fees will vary depending on how you choose to bring Elemental to your institution.
        </p>
        <p>
            If you opt to bring in an independent instructor, fees are set by that instructor. The fee to bring Dr. Holtzman, the director of Elemental, to your institution to present the seminar is variable depending on travel costs.
        </p>
        <p>
            If you opt to certify instructors on your campus, certification fees start at $375. Individual certification is permanent, provided that the instructor teaches the course at least once every 12 months and follows other standards set by the program.
        </p>
        <ul>
            <li>
                In addition to the certification costs, each instructor must have access to the multimedia module that is used to teach the program.  There is a one-time fee of $150 per instructor.
            </li>
            <li>
                Instructors or the institutions for which they offer Elemental as a condition of their employment must carry appropriate insurance.
            </li>
            <li>
                Lastly, each student who completes Elemental should receive access to the online student review materials.  These cost $5 per student.
            </li>
        </ul>
    </dd>



    <dt id="faq_support">
        What support do you offer for seminar registration and other logistics?
    </dt>
    <dd>
        <p>
            All certified Elemental instructors have access to our
            online course-registration system at <a href="http://www.elementalprotection.org/">www.elementalprotection.org</a>. All student
            registrations, fees charged to students (if any), and student review module
            access and use are facilitated through the website. We also offer support for
            training classroom assistants through the website.
        </p>
    </dd>



    <dt id="faq_qualifications">
        Who would make a good Elemental instructor?
    </dt>
    <dd>
        <p>
            Our instructors come from a variety of backgrounds.
            Some have had martial arts experience, whereas others do not. Some have
            backgrounds in higher education. Many hold advanced degrees in social sciences
            and related areas. We have found that it is important for instructors to:
        </p>
        <ul>
            <li>
                Communicate clearly about sensitive and complex topics. This includes having good listening skills.
            </li>
            <li>
                Be able to develop good rapport with the target audience.
            </li>
            <li>
                Demonstrate unwavering professionalism. This is crucial, given the nature of the seminar.
            </li>
            <li>
                Show enthusiasm.
            </li>
            <li>
                Have strong trouble-shooting and related instructional skills.
            </li>
            <li>
                Have strong organizational skills.
            </li>
            <li>
                Demonstrate leadership potential.
            </li>
            <li>
                Demonstrate
                comfort working in physically close quarters with others. This is especially
                important for demonstrating some of the physical skills in the seminar, which
                include grappling on beds, couches, and on the ground.
            </li>
        </ul>
    </dd>



    <dt id="faq_certification">
        How does someone become an Elemental instructor?
    </dt>
    <dd>
        <p>
            Instructor certification has two phases. First,
            trainees complete a nine-lesson online course that covers the background and
            philosophy behind the program, the science underlying the seminar's
            development, the contexts of the scenarios presented to students during the
            seminar, the verbal and physical techniques taught during the seminar
            (including critical points and trouble-shooting advice), and seminar logistics.
            This portion of the training sequence requires about 20-25 hours of time and
            requires passing grades of 80% or better in each of the 9 modules. Second, upon
            completion of the online course, trainees demonstrate their ability to teach
            the techniques and have an opportunity to receive additional feedback during a
            face-to-face or webcast session with Dr. Holtzman, which they must
            also pass with a score of 80% or better.
        </p>
        <p>
            Once certified, instructors maintain their certifications indefinitely
            as long as they teach a full session of the seminar at least once per 12 months
            and follow other guidelines.
        </p>
    </dd>



    <dt id="faq_personnel">
        What are the personnel needs?
    </dt>
    <dd>
        <p>
            Needs will vary depending on the number of students being taught. Consider restricting enrollment to 40 students per session; 20-30 students is ideal.
        </p>
        <p>
            Each session should have the following available:
        </p>
        <ul>
            <li>
                1 Elemental instructor (but may team teach if desired)
            </li>
            <li>
                1 Assistant (a.k.a., "Creeper") for every 10-15 students enrolled. It is strongly recommended that you have a minimum of 2 "Creepers"—one male and one female.
            </li>
            <li>
                You are encouraged to have a counselor on call in case a participant encounters content that triggers memories of prior assault experiences. Basic precautions should also be taken with regard to first aid.
            </li>
        </ul>
    </dd>



    <dt id="faq_equipment">
        What are the equipment needs for Elemental?
    </dt>
    <dd>
        <p>
            The seminar requires several pieces of equipment.  If hiring an independent instructor, the equipment needs will be shared between the instructor and institution in the following ways:
        </p>

        <em>Instructor-Provided Supplies</em>
        <ul>
            <li>
                Laptops
            </li>
            <li>
                Instructional materials
            </li>
            <li>
                Protective gear for trained assistants (see personnel needs)
            </li>
            <li>
                Refreshments, nametags, handouts, and other related incidentals
            </li>
        </ul>

        <em>Institution-Provided Supplies</em>
        <ul>
            <li>
                Seminar space that includes reliable internet access
            </li>
            <li>
                A large-screen audiovisual setup with computer connections
            </li>
            <li>
                2-4 residence hall mattresses, depending on the number of attendees (mattresses will be double stacked)
            </li>
            <li>
                Gymnastic or wrestling mats sufficient to cover the floor around the beds
            </li>
            <li>
                1-2 very well-padded couches (optional – program can be modified to rely on mattresses only)
            </li>
        </ul>

        <p>
            If you certify instructors on your campus, your institution will supply all equipment.  Most colleges and universities have all needed supplies on hand.  The one exception is the protective gear for assistants.  This can be assembled from components or purchased outright as a force-on-force suit.
        </p>
    </dd>



    <dt id="faq_bestpractices">
        Does Elemental incorporate best practices in the field?
    </dt>
    <dd>
        <p>
            Yes. A long, well-developed research history suggests
            that the following traits contribute to sexual assault protection programming
            effectiveness in primary prevention and risk reduction efforts:
        </p>
        <p>
            Comprehensiveness: Because there is considerable variety in the ways
            that potential assaults may unfold, it is important to present participants
            with a variety of realistic scenarios and to provide them with a variety of
            appropriate tools for addressing the range of circumstances that they are most
            likely to face. Elemental incorporates a wide range of scenarios and response
            tools while addressing the social and other contextual factors that promote
            assault.
        </p>
        <p>
            Appropriate timing: Interventions should be deployed at a time that is
            early enough to be effective and in a manner that is age-appropriate. Elemental
            is designed for those in their late teens and early twenties; we recommend it
            for high school and college audiences.
        </p>
        <p>
            Length of program: Longer programs are more effective. Not only does
            the Elemental seminar incorporate 6 hours of training, but participants leave
            the seminar with a set of review materials that promote regular practice over
            the course of a year.
        </p>
        <p>
            Varied teaching methods: Better outcomes are achieved when multiple
            modes of delivery are incorporated. Elemental uses a combination of lecture,
            guided discussion, video, role-playing, and skill-building practice.
        </p>
        <p>
            Facilitator training: Better outcomes are obtained with well-trained
            facilitators. All of our instructors have at least 25-30 hours of training in
            the Elemental curriculum. Many also have advanced degrees in related social
            science fields, extensive martial arts experience, and other supplemental
            training.
        </p>
        <p>
            Promotion of positive relationships among participants: Programs that
            foster positive social connections demonstrate better outcomes. Elemental
            encourages building ongoing friendships and interaction among participants
            beyond the program, and seeks cooperative relationships in the broader
            community (e.g., with administrators and parents).
        </p>
        <p>
            Use of culturally relevant curriculum: Programs that are culturally
            relevant to their target audience are more successful in achieving their goals.
            Elemental explicitly accounts for and addresses the roles of gender and sexual
            orientation in shaping participants' experiences.
        </p>
        <p>
            Theoretical grounding: Programs that are grounded in theoretical
            perspectives on behavior change are more likely to achieve their goals.
            Elemental was developed on a foundation of scientific testing and inquiry. The
            program fosters behavior change through repeated simulation and role playing.
            The program demonstrates long-term effectiveness in changing attitudes and
            beliefs associated with successful protective behavior.
        </p>
        <p>
            Contextualized awareness training: Programs are more effective when
            they teach contextual awareness. The scenarios in Elementals' films and
            simulations are based on common, real-life situations ranging from stranger
            assaults to the more common assaults by friends, acquaintances, and intimate
            partners in a variety of public and private interactions. Participants evaluate
            the warning signs present through collective discussion, as well as appropriate
            early intervention.
        </p>
        <p>
            Verbal response options: Programs that include verbal response options
            are more effective. Elemental's curriculum incorporates a variety of simple and
            effective verbal strategies drawn from scholarship on the social psychology of
            influence and related fields.
        </p>
        <p>
            Physical techniques: Programs that incorporate physical strategies are
            more effective. Elemental teaches a variety of physical techniques&mdash;including
            non-violent approaches&mdash;that are designed to facilitate communication, escape,
            and (when needed) incapacitation of an aggressor.
        </p>
        <p>
            Address contextual and psychological factors that impede
            self-protective responses: Because a person's willingness to use violent
            physical responses is inhibited when an aggressor is an acquaintance, programs
            will be more effective when they include well-chosen verbal and non-violent
            physical options in their curriculum. Since most aggressors in sexual assaults
            are known to their victims, not only does Elemental's curriculum include
            non-violent physical and verbal techniques, but it focuses primarily on those
            options. This approach is coupled with awareness training on the effects of
            gender role socialization to help participants understand why they may feel
            reluctance to resist.
        </p>
        <p>
            Develop comfort with a variety of response options: Because there is
            considerable diversity both in how assaults unfold and in participants'
            pre-existing knowledge and dispositions, programs are more effective if they
            teach a variety of response techniques. Elemental teaches an assortment of
            effective verbal and physical techniques using a carefully developed
            instructional sequence to help students build on their knowledge and success as
            the program progresses.
        </p>
    </dd>



    <dt id="faq_victimblaming">
        Isn't self-defense programming just victim-blaming?
    </dt>
    <dd>
        <p>
            Due to a long history of blaming victims for their own assaults (e.g.,
            the notion that victims were "asking" for sex because they were wearing certain
            clothing, because they were drinking, because they went out alone), there are
            some who might assert that self-defense training unfairly lays more
            responsibility at the feet of potential victims, and that programming should
            instead focus on efforts to change the behavior of potential assailants.
            Victims have indeed been blamed far too often and for far too long, and we
            applaud efforts to reduce assault through gender-role awareness training,
            bystander intervention programs, and similar efforts (collectively known as
            "primary prevention" programming).
        </p>
        <p>
            However, such efforts cannot be expected to be successful in
            eliminating sexual assault on their own. Bystander intervention faces
            formidable barriers: Most assaults occur in private, with large percentages
            taking place within the victims' own homes. Sexual consent is often
            communicated in subtle ways that may not be obvious to bystanders. Moreover,
            consent is considered a private decision, especially in our culture, which is
            becoming increasingly individualistic. Efforts to reduce male aggression work
            against a lifetime of socialization by powerful cultural and subcultural forces
            that promote both sexual aggressiveness and a sexual double standard that
            privileges men. In such an environment, effective self-protection programming
            plays an important role. Self-protection efforts need not blame; they can
            empower.
        </p>
        <p>
            Research shows that <i>well-crafted</i> self-protection programming
            (often referred to as "risk reduction" programming) is effective in reducing
            assault rates. Moreover, even advocates for primary prevention generally
            recognize that self-defense programs are an important part of a multi-faceted
            approach to prevention. Elemental is proud to be an effective partner in the
            movement to end sexual assault.
        </p>
    </dd>



    <dt id="faq_fit">
        How does Elemental fit with other government-mandated and optional programming?
    </dt>
    <dd>
        <p>
            There are a number of efforts at the federal, state, and local levels to reduce sexual assault. The federal SaVE Act, for example, requires (among other mandates) that college campuses include programming for all incoming students, faculty, and staff, as well as maintain ongoing programming designed to reduce assault. Elemental encompasses facets of both primary prevention and risk reduction programming and is meant to augment and dovetail with existing programming offered by an institution.
        </p>
    </dd>
</dl>

<?php $this->Html->script('vendor/jquery.scrollTo.min.js', array('inline' => false)); ?>
<?php $this->Js->buffer("
    $('ul.faq a').click(function (event) {
        event.preventDefault();
        var target = $(this).attr('href');
        $(window).scrollTo($(target), 1000);
    });
"); ?>