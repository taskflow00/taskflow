-- Remove previous versions of these policies, if they exist
DROP POLICY IF EXISTS "Clients can update their own projects"
ON public.projects;

DROP POLICY IF EXISTS "Clients can delete their own projects"
ON public.projects;

DROP POLICY IF EXISTS "Clients can update their own open projects"
ON public.projects;

DROP POLICY IF EXISTS "Clients can delete their own open projects"
ON public.projects;


-- Clients can update their own open, unassigned projects
CREATE POLICY "Clients can update their own open projects"
ON public.projects
FOR UPDATE
TO authenticated
USING (
    client_id = (SELECT auth.uid())
    AND status = 'open'
    AND freelancer_id IS NULL
)
WITH CHECK (
    client_id = (SELECT auth.uid())
    AND status = 'open'
    AND freelancer_id IS NULL
);


-- Clients can delete their own open, unassigned projects
CREATE POLICY "Clients can delete their own open projects"
ON public.projects
FOR DELETE
TO authenticated
USING (
    client_id = (SELECT auth.uid())
    AND status = 'open'
    AND freelancer_id IS NULL
);