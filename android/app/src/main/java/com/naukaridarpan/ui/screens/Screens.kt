package com.naukaridarpan.ui.screens

import androidx.compose.foundation.*
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.*
import androidx.compose.foundation.lazy.grid.*
import androidx.compose.foundation.shape.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.draw.*
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.*
import coil.compose.AsyncImage
import com.naukaridarpan.data.models.*
import com.naukaridarpan.ui.theme.*

// ── HOME SCREEN ───────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HomeScreen(
    categories: List<Category>,
    featuredExams: List<ExamPaper>,
    freeExams: List<ExamPaper>,
    onCategoryClick: (Category) -> Unit,
    onExamClick: (ExamPaper) -> Unit,
    onSearchClick: () -> Unit,
    onBlogClick: () -> Unit,
) {
    LazyColumn(modifier = Modifier.fillMaxSize().background(Cream)) {
        // Hero banner
        item {
            Box(
                modifier = Modifier.fillMaxWidth()
                    .background(Teal)
                    .padding(20.dp)
            ) {
                Column {
                    Text("Naukaridarpan", style = MaterialTheme.typography.headlineMedium, color = Color.White)
                    Text("India's #1 Exam Marketplace", style = MaterialTheme.typography.bodyMedium, color = Color.White.copy(alpha = 0.75f))
                    Spacer(Modifier.height(16.dp))
                    // Search bar
                    OutlinedTextField(
                        value = "",
                        onValueChange = {},
                        modifier = Modifier.fillMaxWidth(),
                        placeholder = { Text("Search UPSC, SSC, Banking…", color = InkLight) },
                        leadingIcon = { Icon(Icons.Default.Search, null, tint = InkLight) },
                        shape = RoundedCornerShape(50),
                        colors = OutlinedTextFieldDefaults.colors(
                            unfocusedContainerColor = Color.White,
                            focusedContainerColor   = Color.White,
                            unfocusedBorderColor    = Color.Transparent,
                            focusedBorderColor      = Saffron,
                        ),
                        readOnly = true,
                        singleLine = true,
                        interactionSource = remember { MutableInteractionSource() }.also {
                            LaunchedEffect(it) { onSearchClick() }
                        },
                    )
                }
            }
        }

        // Categories
        item {
            Column(modifier = Modifier.padding(16.dp)) {
                SectionHeader("Browse by Category", "")
                LazyRow(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    items(categories) { cat ->
                        CategoryChip(cat, onClick = { onCategoryClick(cat) })
                    }
                }
            }
        }

        // Featured exams
        if (featuredExams.isNotEmpty()) {
            item {
                Column(modifier = Modifier.padding(horizontal = 16.dp)) {
                    SectionHeader("Popular Mock Tests", "View all")
                }
            }
            item {
                LazyRow(
                    horizontalArrangement = Arrangement.spacedBy(12.dp),
                    contentPadding = PaddingValues(horizontal = 16.dp),
                ) {
                    items(featuredExams) { exam ->
                        ExamCard(exam = exam, onClick = { onExamClick(exam) }, modifier = Modifier.width(240.dp))
                    }
                }
                Spacer(Modifier.height(8.dp))
            }
        }

        // Free papers
        if (freeExams.isNotEmpty()) {
            item {
                Column(modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp)) {
                    SectionHeader("Free PYQ Papers", "View all")
                    freeExams.take(4).forEach { exam ->
                        ExamListItem(exam = exam, onClick = { onExamClick(exam) })
                        HorizontalDivider(color = BorderColor.copy(alpha = 0.5f))
                    }
                }
            }
        }

        item { Spacer(Modifier.height(80.dp)) }
    }
}

// ── EXAM BROWSE SCREEN ────────────────────────────────────────────────────

@Composable
fun BrowseScreen(
    exams: List<ExamPaper>,
    categories: List<Category>,
    isLoading: Boolean,
    onExamClick: (ExamPaper) -> Unit,
    onFilterChange: (String?, String?, String?) -> Unit,
    onLoadMore: () -> Unit,
) {
    var selectedCategory by remember { mutableStateOf<String?>(null) }
    var selectedPrice    by remember { mutableStateOf<String?>(null) }

    Column(Modifier.fillMaxSize().background(Cream)) {
        // Filter chips
        LazyRow(
            contentPadding = PaddingValues(horizontal = 16.dp, vertical = 10.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            item {
                FilterChip(selected = selectedPrice == "free", onClick = {
                    selectedPrice = if (selectedPrice == "free") null else "free"
                    onFilterChange(selectedCategory, selectedPrice, null)
                }, label = { Text("Free Only") })
            }
            items(categories.take(8)) { cat ->
                FilterChip(selected = selectedCategory == cat.slug, onClick = {
                    selectedCategory = if (selectedCategory == cat.slug) null else cat.slug
                    onFilterChange(selectedCategory, selectedPrice, null)
                }, label = { Text(cat.name) })
            }
        }

        if (isLoading) {
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator(color = Saffron)
            }
        } else {
            LazyVerticalGrid(
                columns = GridCells.Fixed(2),
                contentPadding = PaddingValues(16.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                items(exams) { exam ->
                    ExamCard(exam = exam, onClick = { onExamClick(exam) })
                }
                if (exams.isNotEmpty()) {
                    item(span = { GridItemSpan(2) }) {
                        Button(
                            onClick = onLoadMore,
                            modifier = Modifier.fillMaxWidth(),
                            colors = ButtonDefaults.outlinedButtonColors(),
                        ) { Text("Load More") }
                    }
                }
            }
        }
    }
}

// ── EXAM DETAIL SCREEN ────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ExamDetailScreen(
    exam: ExamPaper,
    isPurchased: Boolean,
    isLoading: Boolean,
    onBuyClick: () -> Unit,
    onStartExam: (Int) -> Unit,
    onBack: () -> Unit,
) {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text(exam.title, maxLines = 1, overflow = TextOverflow.Ellipsis) },
                navigationIcon = { IconButton(onClick = onBack) { Icon(Icons.Default.ArrowBack, null) } },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = Teal, titleContentColor = Color.White, navigationIconContentColor = Color.White),
            )
        },
        bottomBar = {
            Surface(shadowElevation = 8.dp) {
                Box(Modifier.padding(16.dp)) {
                    if (isPurchased) {
                        Button(
                            onClick = { onStartExam(exam.id) },
                            modifier = Modifier.fillMaxWidth().height(52.dp),
                            colors = ButtonDefaults.buttonColors(containerColor = Teal),
                            shape = RoundedCornerShape(12.dp),
                        ) { Text("Start Exam →", fontWeight = FontWeight.Bold, fontSize = 16.sp) }
                    } else {
                        Button(
                            onClick = onBuyClick,
                            modifier = Modifier.fillMaxWidth().height(52.dp),
                            colors = ButtonDefaults.buttonColors(containerColor = Saffron),
                            shape = RoundedCornerShape(12.dp),
                            enabled = !isLoading,
                        ) {
                            if (isLoading) CircularProgressIndicator(Modifier.size(20.dp), color = Color.White)
                            else Text(if (exam.isFree) "Get Free Access →" else "Buy Now — ₹${exam.studentPrice.toInt()}", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                        }
                    }
                }
            }
        }
    ) { padding ->
        LazyColumn(contentPadding = PaddingValues(16.dp), modifier = Modifier.padding(padding)) {
            // Tags
            item {
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp), modifier = Modifier.padding(bottom = 12.dp)) {
                    exam.category?.let { SurfaceChip(it.name, TealLight, Teal) }
                    SurfaceChip(exam.difficulty.replaceFirstChar { it.uppercase() }, SaffronLight, SaffronDark)
                    if (exam.isFree) SurfaceChip("Free", Color(0xFFF0FFF4), SuccessGreen)
                }
            }

            // Stats row
            item {
                Row(
                    modifier = Modifier.fillMaxWidth().background(Color(0xFFF5F0EB), RoundedCornerShape(12.dp)).padding(16.dp),
                    horizontalArrangement = Arrangement.SpaceAround,
                ) {
                    StatItem("📝", "${exam.totalQuestions}", "Questions")
                    StatItem("⏱", "${exam.durationMinutes}", "Minutes")
                    StatItem("🏆", "${exam.maxMarks}", "Marks")
                    StatItem("🔄", "${exam.maxRetakes}", "Retakes")
                }
                Spacer(Modifier.height(16.dp))
            }

            // Description
            exam.description?.let {
                item {
                    Card(modifier = Modifier.fillMaxWidth(), colors = CardDefaults.cardColors(containerColor = Color.White)) {
                        Column(Modifier.padding(16.dp)) {
                            Text("About This Exam", fontWeight = FontWeight.SemiBold, fontSize = 15.sp, modifier = Modifier.padding(bottom = 8.dp))
                            Text(it, style = MaterialTheme.typography.bodyMedium, color = InkSecondary)
                        }
                    }
                    Spacer(Modifier.height(12.dp))
                }
            }

            // Tags
            exam.tags?.let { tags ->
                if (tags.isNotEmpty()) {
                    item {
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                            tags.take(6).forEach { SurfaceChip(it, Color(0xFFF5F0EB), InkSecondary) }
                        }
                    }
                }
            }
        }
    }
}

// ── TAKE EXAM SCREEN (WebView kiosk) ─────────────────────────────────────

@Composable
fun TakeExamScreen(
    examUrl: String,
    examTitle: String,
    durationMinutes: Int,
    onFinished: () -> Unit,
) {
    var timeLeft by remember { mutableStateOf(durationMinutes * 60) }

    LaunchedEffect(Unit) {
        while (timeLeft > 0) {
            kotlinx.coroutines.delay(1000)
            timeLeft--
        }
        onFinished()
    }

    Column(Modifier.fillMaxSize().background(Color(0xFF0A4950))) {
        // Secure exam top bar
        Row(
            modifier = Modifier.fillMaxWidth().background(Teal).padding(horizontal = 16.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Text(examTitle, color = Color.White, fontWeight = FontWeight.SemiBold, maxLines = 1, overflow = TextOverflow.Ellipsis, modifier = Modifier.weight(1f))
            Surface(color = Color.White.copy(alpha = 0.15f), shape = RoundedCornerShape(6.dp)) {
                Text(
                    text = "%02d:%02d".format(timeLeft / 60, timeLeft % 60),
                    color = if (timeLeft < 300) Color(0xFFFF5252) else Color.White,
                    fontWeight = FontWeight.Bold,
                    fontSize = 18.sp,
                    modifier = Modifier.padding(horizontal = 12.dp, vertical = 6.dp),
                )
            }
        }

        // WebView — secure TAO exam
        SecureWebView(
            url = examUrl,
            modifier = Modifier.fillMaxSize(),
            onPageFinished = {},
        )
    }
}

// ── RESULTS SCREEN ────────────────────────────────────────────────────────

@Composable
fun ResultsScreen(
    attempts: List<ExamAttempt>,
    isLoading: Boolean,
    onAttemptClick: (ExamAttempt) -> Unit,
) {
    if (isLoading) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator(color = Saffron)
        }
        return
    }

    if (attempts.isEmpty()) {
        Box(Modifier.fillMaxSize().padding(32.dp), contentAlignment = Alignment.Center) {
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                Text("📊", fontSize = 48.sp)
                Spacer(Modifier.height(12.dp))
                Text("No attempts yet", fontWeight = FontWeight.SemiBold, fontSize = 18.sp)
                Text("Take your first exam to see results here", color = InkLight, textAlign = androidx.compose.ui.text.style.TextAlign.Center, modifier = Modifier.padding(top = 8.dp))
            }
        }
        return
    }

    LazyColumn(contentPadding = PaddingValues(16.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
        items(attempts) { attempt ->
            AttemptCard(attempt = attempt, onClick = { onAttemptClick(attempt) })
        }
    }
}

// ── LOGIN SCREEN ──────────────────────────────────────────────────────────

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LoginScreen(
    isLoading: Boolean,
    error: String?,
    onLogin: (String, String) -> Unit,
    onRegisterClick: () -> Unit,
) {
    var email    by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var passVisible by remember { mutableStateOf(false) }

    Box(Modifier.fillMaxSize().background(Cream)) {
        Column(
            modifier = Modifier.fillMaxWidth().padding(24.dp).align(Alignment.Center),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            // Logo
            Box(
                modifier = Modifier.size(80.dp).clip(CircleShape).background(Teal),
                contentAlignment = Alignment.Center,
            ) {
                Text("N", color = Color.White, fontSize = 36.sp, fontWeight = FontWeight.Bold)
            }
            Spacer(Modifier.height(12.dp))
            Text("Naukaridarpan", fontSize = 24.sp, fontWeight = FontWeight.Normal, color = Teal)
            Text("India's Exam Marketplace", fontSize = 13.sp, color = InkLight)
            Spacer(Modifier.height(32.dp))

            Card(
                modifier = Modifier.fillMaxWidth(),
                colors = CardDefaults.cardColors(containerColor = Color.White),
                shape = RoundedCornerShape(16.dp),
            ) {
                Column(Modifier.padding(20.dp)) {
                    error?.let {
                        Text(it, color = ErrorRed, fontSize = 13.sp, modifier = Modifier.padding(bottom = 12.dp))
                    }

                    OutlinedTextField(value = email, onValueChange = { email = it }, label = { Text("Email Address") },
                        modifier = Modifier.fillMaxWidth(), singleLine = true, shape = RoundedCornerShape(10.dp))
                    Spacer(Modifier.height(12.dp))
                    OutlinedTextField(value = password, onValueChange = { password = it }, label = { Text("Password") },
                        modifier = Modifier.fillMaxWidth(), singleLine = true, shape = RoundedCornerShape(10.dp),
                        visualTransformation = if (passVisible) androidx.compose.ui.text.input.VisualTransformation.None else androidx.compose.ui.text.input.PasswordVisualTransformation(),
                        trailingIcon = { IconButton(onClick = { passVisible = !passVisible }) {
                            Icon(if (passVisible) Icons.Outlined.VisibilityOff else Icons.Outlined.Visibility, null)
                        }})
                    Spacer(Modifier.height(20.dp))
                    Button(
                        onClick = { onLogin(email.trim(), password) },
                        modifier = Modifier.fillMaxWidth().height(50.dp),
                        enabled = !isLoading && email.isNotBlank() && password.isNotBlank(),
                        colors = ButtonDefaults.buttonColors(containerColor = Saffron),
                        shape = RoundedCornerShape(12.dp),
                    ) {
                        if (isLoading) CircularProgressIndicator(Modifier.size(20.dp), color = Color.White)
                        else Text("Sign In", fontWeight = FontWeight.Bold, fontSize = 16.sp)
                    }
                }
            }

            TextButton(onClick = onRegisterClick, modifier = Modifier.padding(top = 12.dp)) {
                Text("Don't have an account? ", color = InkLight)
                Text("Register Free", color = Saffron, fontWeight = FontWeight.SemiBold)
            }
        }
    }
}

// ── SHARED COMPOSABLES ────────────────────────────────────────────────────

@Composable
fun SectionHeader(title: String, action: String, onActionClick: () -> Unit = {}) {
    Row(Modifier.fillMaxWidth().padding(bottom = 12.dp), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
        Text(title, fontWeight = FontWeight.SemiBold, fontSize = 17.sp, color = InkPrimary)
        if (action.isNotEmpty()) TextButton(onClick = onActionClick) { Text(action, color = Saffron, fontSize = 13.sp) }
    }
}

@Composable
fun CategoryChip(category: Category, onClick: () -> Unit) {
    Surface(
        onClick = onClick, shape = RoundedCornerShape(12.dp),
        color = Color.White, border = BorderStroke(1.dp, BorderColor),
        modifier = Modifier.height(80.dp).width(88.dp),
    ) {
        Column(horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center, modifier = Modifier.padding(8.dp)) {
            Text(category.icon ?: "📝", fontSize = 22.sp)
            Spacer(Modifier.height(4.dp))
            Text(category.name, fontSize = 11.sp, fontWeight = FontWeight.Medium, color = InkPrimary, maxLines = 2, overflow = TextOverflow.Ellipsis, textAlign = androidx.compose.ui.text.style.TextAlign.Center)
        }
    }
}

@Composable
fun ExamCard(exam: ExamPaper, onClick: () -> Unit, modifier: Modifier = Modifier) {
    Card(
        onClick = onClick, modifier = modifier,
        colors = CardDefaults.cardColors(containerColor = Color.White),
        shape = RoundedCornerShape(12.dp),
    ) {
        Column {
            Box(Modifier.fillMaxWidth().aspectRatio(16f / 9f).background(TealLight), contentAlignment = Alignment.Center) {
                Text(exam.category?.icon ?: "📝", fontSize = 28.sp)
                if (exam.isFree) {
                    Surface(color = SuccessGreen, shape = RoundedCornerShape(4.dp), modifier = Modifier.align(Alignment.TopStart).padding(8.dp)) {
                        Text("FREE", color = Color.White, fontSize = 10.sp, fontWeight = FontWeight.Bold, modifier = Modifier.padding(horizontal = 6.dp, vertical = 2.dp))
                    }
                }
            }
            Column(Modifier.padding(10.dp)) {
                exam.category?.let { Text(it.name, color = Teal, fontSize = 11.sp, fontWeight = FontWeight.SemiBold) }
                Text(exam.title, fontWeight = FontWeight.SemiBold, fontSize = 13.sp, maxLines = 2, overflow = TextOverflow.Ellipsis, modifier = Modifier.padding(top = 2.dp))
                Row(Modifier.padding(top = 6.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    Text("${exam.totalQuestions}Q", fontSize = 11.sp, color = InkLight)
                    Text("${exam.durationMinutes}m", fontSize = 11.sp, color = InkLight)
                }
                Text(
                    if (exam.isFree) "FREE" else "₹${exam.studentPrice.toInt()}",
                    color = if (exam.isFree) SuccessGreen else Teal,
                    fontWeight = FontWeight.Bold, fontSize = 14.sp, modifier = Modifier.padding(top = 4.dp),
                )
            }
        }
    }
}

@Composable
fun ExamListItem(exam: ExamPaper, onClick: () -> Unit) {
    Row(Modifier.fillMaxWidth().clickable(onClick = onClick).padding(vertical = 12.dp), verticalAlignment = Alignment.CenterVertically) {
        Box(Modifier.size(48.dp).clip(RoundedCornerShape(8.dp)).background(TealLight), contentAlignment = Alignment.Center) {
            Text(exam.category?.icon ?: "📝", fontSize = 20.sp)
        }
        Column(Modifier.weight(1f).padding(horizontal = 12.dp)) {
            Text(exam.title, fontWeight = FontWeight.Medium, fontSize = 14.sp, maxLines = 1, overflow = TextOverflow.Ellipsis)
            Text("${exam.totalQuestions} Qs · ${exam.durationMinutes} min", fontSize = 12.sp, color = InkLight)
        }
        Text(if (exam.isFree) "FREE" else "₹${exam.studentPrice.toInt()}", color = if (exam.isFree) SuccessGreen else Teal, fontWeight = FontWeight.Bold, fontSize = 14.sp)
    }
}

@Composable
fun AttemptCard(attempt: ExamAttempt, onClick: () -> Unit) {
    val pct = attempt.percentage ?: 0.0
    val color = when { pct >= 60 -> SuccessGreen; pct >= 40 -> Gold; else -> ErrorRed }

    Card(onClick = onClick, modifier = Modifier.fillMaxWidth(), colors = CardDefaults.cardColors(containerColor = Color.White), shape = RoundedCornerShape(12.dp)) {
        Row(Modifier.padding(14.dp), verticalAlignment = Alignment.CenterVertically) {
            Box(Modifier.size(52.dp).clip(CircleShape).border(3.dp, color, CircleShape), contentAlignment = Alignment.Center) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text("${pct.toInt()}%", fontWeight = FontWeight.Bold, fontSize = 12.sp, color = color)
                }
            }
            Column(Modifier.weight(1f).padding(start = 12.dp)) {
                attempt.examPaper?.let { Text(it.title, fontWeight = FontWeight.SemiBold, fontSize = 14.sp, maxLines = 1, overflow = TextOverflow.Ellipsis) }
                Row(Modifier.padding(top = 4.dp), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    Text("✓ ${attempt.correctAnswers}", color = SuccessGreen, fontSize = 12.sp, fontWeight = FontWeight.Medium)
                    Text("✗ ${attempt.wrongAnswers}", color = ErrorRed, fontSize = 12.sp, fontWeight = FontWeight.Medium)
                    Text("– ${attempt.unattempted}", color = InkLight, fontSize = 12.sp)
                }
            }
            Icon(Icons.Default.ChevronRight, null, tint = InkLight)
        }
    }
}

@Composable
fun SurfaceChip(text: String, bgColor: Color, textColor: Color) {
    Surface(color = bgColor, shape = RoundedCornerShape(999.dp)) {
        Text(text, color = textColor, fontSize = 11.sp, fontWeight = FontWeight.Bold, modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp))
    }
}

@Composable
fun StatItem(icon: String, value: String, label: String) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Text(icon, fontSize = 20.sp)
        Text(value, fontWeight = FontWeight.Bold, fontSize = 14.sp, color = Teal, modifier = Modifier.padding(top = 2.dp))
        Text(label, fontSize = 11.sp, color = InkLight)
    }
}
