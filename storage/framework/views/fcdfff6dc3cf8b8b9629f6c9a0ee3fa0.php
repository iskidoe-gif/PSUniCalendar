<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals - UniCalendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen">
        <main class="overflow-y-auto p-8">
            <?php if(session('success')): ?>
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Pending Approvals</h1>
                <p class="text-sm text-gray-500 mt-1">Review and approve or reject venue requests.</p>
            </div>

            <?php echo $__env->make('partials.calendar-navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs text-gray-400 uppercase">
                                <th class="py-3 px-4">Event Title</th>
                                <th class="py-3 px-4">Requested By</th>
                                <th class="py-3 px-4">Campus</th>
                                <th class="py-3 px-4">Venue</th>
                                <th class="py-3 px-4">Date & Time</th>
                                <th class="py-3 px-4">Documents</th>
                                <th class="py-3 px-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="py-3 px-4 font-semibold text-gray-800"><?php echo e($request->title); ?></td>
                                    <td class="py-3 px-4"><?php echo e($request->name); ?><br><span class="text-xs text-slate-500"><?php echo e($request->email); ?></span></td>
                                    <td class="py-3 px-4"><?php echo e($request->campus ?? 'Not specified'); ?></td>
                                    <td class="py-3 px-4"><?php echo e($request->venue_name); ?></td>
                                    <td class="py-3 px-4"><?php echo e(date('M d, Y', strtotime($request->start_datetime))); ?><br><span class="text-xs text-slate-500"><?php echo e(date('g:i A', strtotime($request->start_datetime))); ?> - <?php echo e(date('g:i A', strtotime($request->end_datetime))); ?></span></td>
                                    <td class="py-3 px-4">
                                        <?php if(!empty($request->digital_documents)): ?>
                                            <div class="space-y-2">
                                                <?php $__currentLoopData = $request->digital_documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <a href="<?php echo e(asset('storage/' . $doc)); ?>" target="_blank" class="block text-xs text-indigo-600 hover:underline">Document <?php echo e($loop->iteration); ?></a>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-500">None uploaded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 space-x-2">
                                        <form method="POST" action="<?php echo e(route('superadmin.approve', ['id' => $request->id])); ?>" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold">Approve</button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route('superadmin.reject', ['id' => $request->id])); ?>" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-bold">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="py-6 px-4 text-center text-sm text-slate-500">No pending venue requests at this time.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php /**PATH C:\Users\Kaye Fernandez\Herd\capstone10\resources\views/superadmin/pending-approvals.blade.php ENDPATH**/ ?>