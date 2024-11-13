<?php

ini_set('display_errors', 'On'); // Set to 'Off' in production
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$password = 'root';
$database = 'onefilephp';

$conn = mysqli_connect($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current URI
$requestUri = $_SERVER['REQUEST_URI'];

// Remove the base directory '/onefilephp' from the URI
$basePath = '/onefilephp';
$requestUri = str_replace($basePath, '', $requestUri);

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>onefilephp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#111927]">
<header>
    <nav class="bg-white border-gray-200 dark:bg-gray-900">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <a href="/onefilephp/" class="flex items-center space-x-3 rtl:space-x-reverse">
                <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Job Board</span>
            </a>
            <button data-collapse-toggle="navbar-default" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-default" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>
            <div class="hidden w-full md:block md:w-auto" id="navbar-default">
                <ul class="font-medium flex flex-col p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                    <li>
                        <a href="/onefilephp" class="block py-2 px-3 text-white bg-blue-700 rounded md:bg-transparent md:text-blue-700 md:p-0 dark:text-white md:dark:text-blue-500" aria-current="page">Home</a>
                    </li>
                    <li>
                        <a href="/onefilephp/create" class="block py-2 px-3 text-white bg-blue-700 rounded md:bg-transparent md:text-blue-700 md:p-0 dark:text-white md:dark:text-blue-500" aria-current="page">Create Job</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<?php
// Handle Create Job form submission
if ($requestUri == '/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $job_link = mysqli_real_escape_string($conn, $_POST['job_link']);

    // Insert job into the database
    $stmt = $conn->prepare("INSERT INTO jobs (title, content, job_link) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $job_link);
    $stmt->execute();
    $stmt->close();

    echo "<p class='text-white'>Job posted successfully!</p>";
}

if (isset($_GET['id'])) {
    $jobId = $_GET['id'];

    // Ensure the ID is numeric
    if (is_numeric($jobId)) {
        $jobId = (int)$jobId;

        // Use a prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            ?>
            <section class="bg-[#111927] py-[50px] h-screen">
                <div class="max-w-[900px] mx-auto px-4">
                    <!-- Display the job's title and content -->
                    <div class="flex flex-col gap-2">
                        <h2 class="text-white text-3xl"><?php echo htmlspecialchars($row['title']); ?></h2>
                        <p class="text-white"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                        <a href="<?php echo nl2br(htmlspecialchars($row['job_link'])); ?>"
                           class="max-w-fit inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Apply For Job
                            <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>
            <?php
        } else {
            echo 'Job not found';
        }

        $stmt->close();
    } else {
        echo 'Invalid job id';
    }
} elseif ($requestUri == '/') {
    ?>
    <section class="bg-[#111927] pt-[50px]">
        <div class="max-w-[900px] mx-auto px-4">
            <h1 class="text-white text-center font-bold text-[30px] sm:text-[35px] md:text-[40px]">Welcome to onefilephp Jobs</h1>
        </div>
    </section>

    <section class="bg-[#111927] py-[50px]">
        <div class="max-w-[900px] mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php
                $sql = "SELECT * FROM jobs";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="min-w-[100%] p-5 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                            <a href="?id=<?php echo $row['id']; ?>">
                                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['title']); ?></h5>
                            </a>
                            <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><?php echo htmlspecialchars($row['content']); ?></p>
                            <a href="?id=<?php echo $row['id']; ?>"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                View Job
                                <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                </svg>
                            </a>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
    <?php
} elseif ($requestUri == '/create') {
    ?>
    <section class="bg-[#111927] py-[50px]">
        <div class="max-w-[900px] mx-auto px-4">
            <h2 class="text-white text-2xl font-semibold">Create New Job</h2>
            <form method="POST">
                <div class="mb-4">
                    <label for="title" class="text-white">Job Title</label>
                    <input type="text" name="title" id="title" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div class="mb-4">
                    <label for="content" class="text-white">Job Description</label>
                    <textarea name="content" id="content" rows="4" class="w-full p-2 border border-gray-300 rounded" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="job_link" class="text-white">Job Link</label>
                    <input type="url" name="job_link" id="job_link" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <button type="submit"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Post Job
                </button>
            </form>
        </div>
    </section>
    <?php
}
?>

</body>
</html>
