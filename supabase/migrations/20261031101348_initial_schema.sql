
-- =====================================
-- 1. PROFILES TABLE
-- Stores all TaskFlow user information
-- =====================================

CREATE TABLE public.profiles (
    id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    full_name TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('client', 'freelancer', 'admin')),
    avatar_url TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);



-- =====================================
-- 2. PROJECTS TABLE
-- Stores projects created by clients
-- =====================================

CREATE TABLE public.projects (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    client_id UUID NOT NULL
        REFERENCES public.profiles(id) ON DELETE CASCADE,

    freelancer_id UUID
        REFERENCES public.profiles(id) ON DELETE SET NULL,

    title TEXT NOT NULL,
    description TEXT,
    budget NUMERIC(10,2),
    due_date DATE,

    status TEXT NOT NULL DEFAULT 'open'
        CHECK (status IN ('open', 'in_progress', 'completed', 'cancelled')),

    created_at TIMESTAMPTZ DEFAULT NOW()
);



-- =====================================
-- 3. PROJECT INVITATIONS TABLE
-- Handles client-freelancer connections
-- =====================================

CREATE TABLE public.project_invitations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    project_id UUID NOT NULL
        REFERENCES public.projects(id) ON DELETE CASCADE,

    freelancer_id UUID NOT NULL
        REFERENCES public.profiles(id) ON DELETE CASCADE,

    message TEXT,

    status TEXT NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'accepted', 'declined')),

    created_at TIMESTAMPTZ DEFAULT NOW(),

    UNIQUE (project_id, freelancer_id)
);


-- =====================================
-- 4. WORK SCHEDULES TABLE
-- Stores expected working hours
-- =====================================

CREATE TABLE public.work_schedules (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    project_id UUID NOT NULL
        REFERENCES public.projects(id) ON DELETE CASCADE,

    freelancer_id UUID NOT NULL
        REFERENCES public.profiles(id) ON DELETE CASCADE,

    daily_hours NUMERIC(4,2) NOT NULL DEFAULT 8
        CHECK (daily_hours > 0 AND daily_hours <= 24),

    created_at TIMESTAMPTZ DEFAULT NOW(),

    UNIQUE (project_id, freelancer_id)
);


-- =====================================
-- 5. TASKS TABLE
-- Stores tasks under each project
-- =====================================

CREATE TABLE public.tasks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    project_id UUID NOT NULL
        REFERENCES public.projects(id) ON DELETE CASCADE,

    title TEXT NOT NULL,
    description TEXT,
    due_date DATE,

    status TEXT NOT NULL DEFAULT 'todo'
        CHECK (status IN ('todo', 'in_progress', 'review', 'completed')),

    created_at TIMESTAMPTZ DEFAULT NOW()
);


-- =====================================
-- 6. TIME ENTRIES TABLE
-- Records freelancer working sessions
-- =====================================

CREATE TABLE public.time_entries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    project_id UUID NOT NULL
        REFERENCES public.projects(id) ON DELETE CASCADE,

    task_id UUID
        REFERENCES public.tasks(id) ON DELETE SET NULL,

    freelancer_id UUID NOT NULL
        REFERENCES public.profiles(id) ON DELETE CASCADE,

    started_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    ended_at TIMESTAMPTZ,

    duration_seconds INTEGER DEFAULT 0
        CHECK (duration_seconds >= 0),

    notes TEXT,

    created_at TIMESTAMPTZ DEFAULT NOW(),

    CHECK (ended_at IS NULL OR ended_at >= started_at)
);



-- =====================================
-- 7. SUBMISSIONS TABLE
-- Stores freelancer submitted work
-- =====================================

CREATE TABLE public.submissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    project_id UUID NOT NULL
        REFERENCES public.projects(id) ON DELETE CASCADE,

    task_id UUID
        REFERENCES public.tasks(id) ON DELETE SET NULL,

    freelancer_id UUID NOT NULL
        REFERENCES public.profiles(id) ON DELETE CASCADE,

    file_path TEXT,
    work_link TEXT,
    message TEXT,

    status TEXT NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'approved', 'revision_requested')),

    feedback TEXT,

    created_at TIMESTAMPTZ DEFAULT NOW()
);



-- =====================================
-- 8. DATABASE INDEXES
-- Improves database query performance
-- =====================================

CREATE INDEX idx_projects_client
ON public.projects(client_id);

CREATE INDEX idx_projects_freelancer
ON public.projects(freelancer_id);

CREATE INDEX idx_invitations_freelancer
ON public.project_invitations(freelancer_id);

CREATE INDEX idx_tasks_project
ON public.tasks(project_id);

CREATE INDEX idx_time_entries_freelancer
ON public.time_entries(freelancer_id);

CREATE INDEX idx_time_entries_project
ON public.time_entries(project_id);

CREATE INDEX idx_submissions_project
ON public.submissions(project_id);


-- =====================================
-- 9. AUTOMATIC PROFILE CREATION
-- Creates a profile after registration
-- =====================================

CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
BEGIN
    INSERT INTO public.profiles (id, full_name, role)
    VALUES (
        NEW.id,
        COALESCE(NEW.raw_user_meta_data ->> 'full_name', 'New User'),
        CASE
            WHEN NEW.raw_user_meta_data ->> 'role'
                IN ('client', 'freelancer')
            THEN NEW.raw_user_meta_data ->> 'role'
            ELSE 'freelancer'
        END
    );

    RETURN NEW;
END;
$$;

CREATE TRIGGER on_auth_user_created
AFTER INSERT ON auth.users
FOR EACH ROW
EXECUTE FUNCTION public.handle_new_user();



-- =====================================
-- 10. ENABLE ROW LEVEL SECURITY
-- =====================================

ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.projects ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.project_invitations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.work_schedules ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.tasks ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.time_entries ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.submissions ENABLE ROW LEVEL SECURITY;


-- =====================================
-- 11. VIEWING POLICIES
-- Controls who can view records
-- =====================================

-- PROFILES
-- Authenticated users can browse user profiles.
CREATE POLICY "Authenticated users can view profiles"
ON public.profiles
FOR SELECT
TO authenticated
USING (true);


-- PROJECTS
-- Only the client and assigned freelancer can view.
CREATE POLICY "Project participants can view projects"
ON public.projects
FOR SELECT
TO authenticated
USING (
    client_id = (SELECT auth.uid())
    OR freelancer_id = (SELECT auth.uid())
);


-- PROJECT INVITATIONS
-- The invited freelancer and project owner can view.
CREATE POLICY "Invitation participants can view invitations"
ON public.project_invitations
FOR SELECT
TO authenticated
USING (
    freelancer_id = (SELECT auth.uid())
    OR EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = project_invitations.project_id
        AND projects.client_id = (SELECT auth.uid())
    )
);


-- WORK SCHEDULES
-- Only project participants can view schedules.
CREATE POLICY "Project participants can view schedules"
ON public.work_schedules
FOR SELECT
TO authenticated
USING (
    EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = work_schedules.project_id
        AND (
            projects.client_id = (SELECT auth.uid())
            OR projects.freelancer_id = (SELECT auth.uid())
        )
    )
);


-- TASKS
-- Only project participants can view tasks.
CREATE POLICY "Project participants can view tasks"
ON public.tasks
FOR SELECT
TO authenticated
USING (
    EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = tasks.project_id
        AND (
            projects.client_id = (SELECT auth.uid())
            OR projects.freelancer_id = (SELECT auth.uid())
        )
    )
);


-- TIME ENTRIES
-- The freelancer and project owner can view work records.
CREATE POLICY "Time entry participants can view records"
ON public.time_entries
FOR SELECT
TO authenticated
USING (
    freelancer_id = (SELECT auth.uid())
    OR EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = time_entries.project_id
        AND projects.client_id = (SELECT auth.uid())
    )
);


-- SUBMISSIONS
-- The freelancer and project owner can view submissions.
CREATE POLICY "Submission participants can view records"
ON public.submissions
FOR SELECT
TO authenticated
USING (
    freelancer_id = (SELECT auth.uid())
    OR EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = submissions.project_id
        AND projects.client_id = (SELECT auth.uid())
    )
);


-- =====================================
-- 12. PROJECT CREATION POLICY
-- Only clients can create their projects
-- =====================================

CREATE POLICY "Clients can create projects"
ON public.projects
FOR INSERT
TO authenticated
WITH CHECK (
    client_id = (SELECT auth.uid())
    AND freelancer_id IS NULL
    AND EXISTS (
        SELECT 1
        FROM public.profiles
        WHERE profiles.id = (SELECT auth.uid())
        AND profiles.role = 'client'
    )
);


-- =====================================
-- 13. INVITATION CREATION POLICY
-- Clients can invite freelancers
-- to their own projects
-- =====================================

CREATE POLICY "Clients can invite freelancers"
ON public.project_invitations
FOR INSERT
TO authenticated
WITH CHECK (
    status = 'pending'
    AND EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = project_invitations.project_id
        AND projects.client_id = (SELECT auth.uid())
        AND projects.freelancer_id IS NULL
    )
    AND EXISTS (
        SELECT 1
        FROM public.profiles
        WHERE profiles.id = project_invitations.freelancer_id
        AND profiles.role = 'freelancer'
    )
);


-- =====================================
-- 14. TASK MANAGEMENT POLICIES
-- Clients can create and manage project tasks
-- =====================================

CREATE POLICY "Clients can create tasks"
ON public.tasks
FOR INSERT
TO authenticated
WITH CHECK (
    EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = tasks.project_id
        AND projects.client_id = (SELECT auth.uid())
    )
);

CREATE POLICY "Clients can update tasks"
ON public.tasks
FOR UPDATE
TO authenticated
USING (
    EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = tasks.project_id
        AND projects.client_id = (SELECT auth.uid())
    )
)
WITH CHECK (
    EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = tasks.project_id
        AND projects.client_id = (SELECT auth.uid())
    )
);


-- =====================================
-- 15. WORK SCHEDULE POLICIES
-- Clients configure assigned freelancers' targets
-- =====================================

CREATE POLICY "Clients can create work schedules"
ON public.work_schedules
FOR INSERT
TO authenticated
WITH CHECK (
    EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = work_schedules.project_id
        AND projects.client_id = (SELECT auth.uid())
        AND projects.freelancer_id = work_schedules.freelancer_id
    )
);

CREATE POLICY "Clients can update work schedules"
ON public.work_schedules
FOR UPDATE
TO authenticated
USING (
    EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = work_schedules.project_id
        AND projects.client_id = (SELECT auth.uid())
        AND projects.freelancer_id = work_schedules.freelancer_id
    )
)
WITH CHECK (
    EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = work_schedules.project_id
        AND projects.client_id = (SELECT auth.uid())
        AND projects.freelancer_id = work_schedules.freelancer_id
    )
);


-- =====================================
-- 16. SUBMISSION CREATION POLICY
-- Freelancers can submit their own work
-- =====================================

CREATE POLICY "Freelancers can create submissions"
ON public.submissions
FOR INSERT
TO authenticated
WITH CHECK (
    freelancer_id = (SELECT auth.uid())
    AND status = 'pending'
    AND EXISTS (
        SELECT 1
        FROM public.projects
        WHERE projects.id = submissions.project_id
        AND projects.freelancer_id = (SELECT auth.uid())
    )
    AND (
        task_id IS NULL
        OR EXISTS (
            SELECT 1
            FROM public.tasks
            WHERE tasks.id = submissions.task_id
            AND tasks.project_id = submissions.project_id
        )
    )
);


-- =====================================
-- 17. ONLY ONE ACTIVE TIMER PER FREELANCER
-- =====================================

CREATE UNIQUE INDEX idx_one_active_timer_per_freelancer
ON public.time_entries (freelancer_id)
WHERE ended_at IS NULL;



-- =====================================
-- 18. SUBMISSION MUST INCLUDE WORK
-- =====================================


ALTER TABLE public.submissions
ADD CONSTRAINT submission_must_include_work
CHECK (
    (
        file_path IS NOT NULL
        AND LENGTH(TRIM(file_path)) > 0
    )
    OR
    (
        work_link IS NOT NULL
        AND LENGTH(TRIM(work_link)) > 0
    )
);


-- =====================================
-- 19. ADMIN CHECK FUNCTION
-- =====================================

CREATE OR REPLACE FUNCTION public.is_taskflow_admin()
RETURNS BOOLEAN
LANGUAGE SQL
SECURITY DEFINER
SET search_path = ''
AS $$
    SELECT EXISTS (
        SELECT 1
        FROM public.profiles
        WHERE id = (SELECT auth.uid())
        AND role = 'admin'
    );
$$;

-- Only authenticated users need to call this function
REVOKE ALL ON FUNCTION public.is_taskflow_admin() FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.is_taskflow_admin() TO authenticated;


-- =====================================
-- 20. ADMIN ACCESS POLICIES
-- Admins can manage all TaskFlow records
-- =====================================

CREATE POLICY "Admins can manage profiles"
ON public.profiles
FOR ALL
TO authenticated
USING (public.is_taskflow_admin())
WITH CHECK (public.is_taskflow_admin());


CREATE POLICY "Admins can manage projects"
ON public.projects
FOR ALL
TO authenticated
USING (public.is_taskflow_admin())
WITH CHECK (public.is_taskflow_admin());


CREATE POLICY "Admins can manage invitations"
ON public.project_invitations
FOR ALL
TO authenticated
USING (public.is_taskflow_admin())
WITH CHECK (public.is_taskflow_admin());


CREATE POLICY "Admins can manage schedules"
ON public.work_schedules
FOR ALL
TO authenticated
USING (public.is_taskflow_admin())
WITH CHECK (public.is_taskflow_admin());


CREATE POLICY "Admins can manage tasks"
ON public.tasks
FOR ALL
TO authenticated
USING (public.is_taskflow_admin())
WITH CHECK (public.is_taskflow_admin());


CREATE POLICY "Admins can manage time entries"
ON public.time_entries
FOR ALL
TO authenticated
USING (public.is_taskflow_admin())
WITH CHECK (public.is_taskflow_admin());


CREATE POLICY "Admins can manage submissions"
ON public.submissions
FOR ALL
TO authenticated
USING (public.is_taskflow_admin())
WITH CHECK (public.is_taskflow_admin());


-- =====================================
-- 21. ACCEPT PROJECT INVITATION
-- =====================================

CREATE OR REPLACE FUNCTION public.accept_project_invitation(
    p_invitation_id UUID
)
RETURNS BOOLEAN
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
DECLARE
    v_project_id UUID;
    v_assigned_freelancer UUID;
    v_project_status TEXT;
BEGIN
    -- Find and lock the freelancer's pending invitation
    SELECT project_id
    INTO v_project_id
    FROM public.project_invitations
    WHERE id = p_invitation_id
      AND freelancer_id = (SELECT auth.uid())
      AND status = 'pending'
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Invitation not found or unavailable';
    END IF;

    -- Lock the project before assigning the freelancer
    SELECT freelancer_id, status
    INTO v_assigned_freelancer, v_project_status
    FROM public.projects
    WHERE id = v_project_id
    FOR UPDATE;

    IF NOT FOUND
       OR v_assigned_freelancer IS NOT NULL
       OR v_project_status <> 'open' THEN
        RAISE EXCEPTION 'Project is no longer available';
    END IF;

    -- Assign freelancer and start the project
    UPDATE public.projects
    SET freelancer_id = (SELECT auth.uid()),
        status = 'in_progress'
    WHERE id = v_project_id;

    -- Accept invitation
    UPDATE public.project_invitations
    SET status = 'accepted'
    WHERE id = p_invitation_id;

    RETURN TRUE;
END;
$$;

REVOKE ALL ON FUNCTION public.accept_project_invitation(UUID) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.accept_project_invitation(UUID) TO authenticated;


-- =====================================
-- 22. DECLINE PROJECT INVITATION
-- =====================================

CREATE OR REPLACE FUNCTION public.decline_project_invitation(
    p_invitation_id UUID
)
RETURNS BOOLEAN
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
BEGIN
    UPDATE public.project_invitations
    SET status = 'declined'
    WHERE id = p_invitation_id
      AND freelancer_id = (SELECT auth.uid())
      AND status = 'pending';

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Invitation not found or unavailable';
    END IF;

    RETURN TRUE;
END;
$$;

REVOKE ALL ON FUNCTION public.decline_project_invitation(UUID) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.decline_project_invitation(UUID) TO authenticated;


-- =====================================
-- 23. START WORK TIMER
-- =====================================

CREATE OR REPLACE FUNCTION public.start_work_timer(
    p_project_id UUID,
    p_task_id UUID DEFAULT NULL
)
RETURNS UUID
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
DECLARE
    v_entry_id UUID;
BEGIN
    -- Check project assignment
    IF NOT EXISTS (
        SELECT 1
        FROM public.projects
        WHERE id = p_project_id
          AND freelancer_id = (SELECT auth.uid())
          AND status = 'in_progress'
    ) THEN
        RAISE EXCEPTION 'You are not assigned to this active project';
    END IF;

    -- Check that the task belongs to this project
    IF p_task_id IS NOT NULL AND NOT EXISTS (
        SELECT 1
        FROM public.tasks
        WHERE id = p_task_id
          AND project_id = p_project_id
    ) THEN
        RAISE EXCEPTION 'Task does not belong to this project';
    END IF;

    -- Create a new timer session
    INSERT INTO public.time_entries (
        project_id,
        task_id,
        freelancer_id,
        started_at
    )
    VALUES (
        p_project_id,
        p_task_id,
        (SELECT auth.uid()),
        clock_timestamp()
    )
    RETURNING id INTO v_entry_id;

    RETURN v_entry_id;
END;
$$;

REVOKE ALL ON FUNCTION public.start_work_timer(UUID, UUID) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.start_work_timer(UUID, UUID) TO authenticated;


-- =====================================
-- 24. PAUSE / STOP WORK TIMER
-- =====================================

CREATE OR REPLACE FUNCTION public.stop_work_timer()
RETURNS INTEGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
DECLARE
    v_duration INTEGER;
    v_ended_at TIMESTAMPTZ;
BEGIN
    v_ended_at := clock_timestamp();

    UPDATE public.time_entries
    SET
        ended_at = v_ended_at,
        duration_seconds = GREATEST(
            0,
            FLOOR(
                EXTRACT(EPOCH FROM (v_ended_at - started_at))
            )::INTEGER
        )
    WHERE freelancer_id = (SELECT auth.uid())
      AND ended_at IS NULL
    RETURNING duration_seconds INTO v_duration;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'No active timer found';
    END IF;

    RETURN v_duration;
END;
$$;

REVOKE ALL ON FUNCTION public.stop_work_timer() FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.stop_work_timer() TO authenticated;


-- =====================================
-- 25. REVIEW FREELANCER SUBMISSION
-- =====================================

CREATE OR REPLACE FUNCTION public.review_submission(
    p_submission_id UUID,
    p_status TEXT,
    p_feedback TEXT DEFAULT NULL
)
RETURNS BOOLEAN
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
DECLARE
    v_project_id UUID;
BEGIN
    IF p_status IS NULL
    OR p_status NOT IN ('approved', 'revision_requested') THEN
        RAISE EXCEPTION 'Invalid review status';
    END IF;

    SELECT project_id
    INTO v_project_id
    FROM public.submissions
    WHERE id = p_submission_id
      AND status = 'pending'
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Submission not found or already reviewed';
    END IF;

    -- Only the project owner can review submissions
    IF NOT EXISTS (
        SELECT 1
        FROM public.projects
        WHERE id = v_project_id
          AND client_id = (SELECT auth.uid())
    ) THEN
        RAISE EXCEPTION 'You are not authorized to review this submission';
    END IF;

    UPDATE public.submissions
    SET
        status = p_status,
        feedback = p_feedback
    WHERE id = p_submission_id;

    RETURN TRUE;
END;
$$;

REVOKE ALL ON FUNCTION public.review_submission(UUID, TEXT, TEXT) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.review_submission(UUID, TEXT, TEXT) TO authenticated;
